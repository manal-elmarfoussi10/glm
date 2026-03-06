<?php

namespace App\Http\Controllers\App;

use App\Contracts\DocumentExtractorInterface;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationInspection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyCustomerController extends Controller
{
    private function ensureCustomerBelongsToCompany(Customer $customer, Company $company): void
    {
        if ($customer->company_id != $company->id) {
            abort(404);
        }
    }

    /** Lookup customer by CIN for reservation wizard: returns customer + stats + risk. */
    public function lookupByCin(Request $request, Company $company): JsonResponse
    {
        $cin = $request->query('cin');
        if (! $cin || strlen(trim($cin)) < 2) {
            return response()->json(['found' => false]);
        }
        $customer = $company->customers()->where('cin', 'like', trim($cin))->first();
        if (! $customer) {
            return response()->json(['found' => false]);
        }
        $totalReservations = $customer->reservations()->count();
        $totalRevenue = (float) $customer->reservations()->where('status', Reservation::STATUS_COMPLETED)->sum('total_price');
        $unpaidBalance = 0;
        foreach ($customer->reservations()->whereIn('payment_status', [Reservation::PAYMENT_UNPAID, Reservation::PAYMENT_PARTIAL])->get() as $r) {
            $unpaidBalance += $r->remaining_amount;
        }
        $damageCount = ReservationInspection::query()
            ->whereHas('reservation', fn ($q) => $q->where('customer_id', $customer->id))
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('type', ReservationInspection::TYPE_OUT)->whereNotNull('damage_checklist');
                    if (DB::connection()->getDriverName() === 'mysql') {
                        $q2->whereRaw('JSON_LENGTH(damage_checklist) > 0');
                    } else {
                        $q2->where('damage_checklist', '!=', '[]');
                    }
                })->orWhere(function ($q2) {
                    $q2->where('type', ReservationInspection::TYPE_IN)->whereNotNull('new_damages')->where('new_damages', '!=', '');
                });
            })
            ->count();
        $risk = 'green';
        if ($customer->is_flagged || $damageCount > 0) {
            $risk = 'red';
        } elseif ($unpaidBalance > 0) {
            $risk = 'yellow';
        }
        return response()->json([
            'found' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'cin' => $customer->cin,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'is_flagged' => (bool) $customer->is_flagged,
            ],
            'total_reservations' => $totalReservations,
            'total_revenue' => round($totalRevenue, 2),
            'unpaid_balance' => round($unpaidBalance, 2),
            'damage_count' => $damageCount,
            'risk' => $risk,
        ]);
    }

    public function index(Request $request, Company $company): View
    {
        $query = $company->customers();

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cin', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('flagged')) {
            if ($request->flagged === '1') {
                $query->where('is_flagged', true);
            } elseif ($request->flagged === '0') {
                $query->where('is_flagged', false);
            }
        }

        $customers = $query->orderBy('name')->paginate(20)->withQueryString();
        $cities = $company->customers()->whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('app.companies.customers.index', [
            'title' => 'Clients – ' . $company->name,
            'company' => $company,
            'customers' => $customers,
            'cities' => $cities,
        ]);
    }

    public function create(Company $company): View
    {
        return view('app.companies.customers.create', [
            'title' => 'Nouveau client – ' . $company->name,
            'company' => $company,
        ]);
    }

    /**
     * Upload a document (CIN or permis), store privately in temp, extract text, return extracted data.
     * Session holds temp paths and extractions for final move on store.
     */
    public function extractDocuments(Request $request, Company $company, DocumentExtractorInterface $extractor): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|string|in:cin_front,cin_back,license',
        ]);

        $file = $request->file('file');
        $sessionKey = 'customer_create_temp.' . $company->id;
        $session = session();
        $data = $session->get($sessionKey, ['paths' => [], 'extractions' => []]);

        $tempDir = 'temp/' . $session->getId();
        $filename = $request->input('type') . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $tempDir . '/' . $filename;

        $disk = Storage::disk('customer_documents');
        $disk->put($path, $file->get());

        $fullPath = $disk->path($path);
        $typeForExtractor = $request->input('type') === 'license' ? 'license' : $request->input('type');
        $extracted = $extractor->extractFromFile($fullPath, $typeForExtractor);

        $data['paths'][$request->input('type')] = $path;
        $data['extractions'][$request->input('type')] = $extracted;
        $data['merged'] = $extractor->mergeExtracted(array_values($data['extractions']));
        $session->put($sessionKey, $data);

        return response()->json([
            'temp_path' => $path,
            'extracted' => $extracted,
            'merged' => $data['merged'],
            'filename' => $file->getClientOriginalName(),
        ]);
    }

    public function downloadDocument(Company $company, Customer $customer, string $type): StreamedResponse
    {
        $this->ensureCustomerBelongsToCompany($customer, $company);

        $field = match ($type) {
            'cin_front' => 'cin_front_path',
            'cin_back' => 'cin_back_path',
            'license' => 'license_document_path',
            default => null,
        };
        if (! $field || ! $customer->$field) {
            abort(404);
        }

        $path = $customer->$field;
        $disk = Storage::disk('customer_documents');
        if ($disk->exists($path)) {
            return $disk->download($path, basename($path), [
                'Content-Type' => $disk->mimeType($path),
            ]);
        }
        // Legacy: documents stored on public disk before private migration
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path)) {
            return $publicDisk->download($path, basename($path), [
                'Content-Type' => $publicDisk->mimeType($path),
            ]);
        }
        abort(404);
    }

    public function store(Request $request, Company $company): RedirectResponse|JsonResponse
    {
        $validated = $this->validateCustomer($request);
        $validated['company_id'] = $company->id;
        $validated['is_flagged'] = $request->boolean('is_flagged');
        unset($validated['cin_front'], $validated['cin_back'], $validated['license_document']);
        $customer = Customer::create($validated);
        $this->moveTempDocumentsToCustomer($request, $company, $customer);
        $this->handleUploads($request, $customer, $company);

        if ($request->wantsJson()) {
            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'cin' => $customer->cin,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'is_flagged' => (bool) $customer->is_flagged,
                ],
            ], 201);
        }

        return redirect()
            ->route('app.companies.customers.show', [$company, $customer])
            ->with('success', 'Client créé.');
    }

    public function show(Company $company, Customer $customer): View
    {
        $this->ensureCustomerBelongsToCompany($customer, $company);
        return view('app.companies.customers.show', [
            'title' => $customer->name . ' – ' . $company->name,
            'company' => $company,
            'customer' => $customer,
        ]);
    }

    public function edit(Company $company, Customer $customer): View
    {
        $this->ensureCustomerBelongsToCompany($customer, $company);
        return view('app.companies.customers.edit', [
            'title' => 'Modifier ' . $customer->name . ' – ' . $company->name,
            'company' => $company,
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, Company $company, Customer $customer): RedirectResponse
    {
        $this->ensureCustomerBelongsToCompany($customer, $company);
        $validated = $this->validateCustomer($request, $customer);
        $validated['is_flagged'] = $request->boolean('is_flagged');
        unset($validated['cin_front'], $validated['cin_back'], $validated['license_document']);
        $customer->update($validated);
        $this->handleUploads($request, $customer, $company);
        return redirect()
            ->route('app.companies.customers.show', [$company, $customer])
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Company $company, Customer $customer): RedirectResponse
    {
        $this->ensureCustomerBelongsToCompany($customer, $company);
        $customer->delete();
        return redirect()
            ->route('app.companies.customers.index', $company)
            ->with('success', 'Client supprimé.');
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'cin' => 'required|string|max:32',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:128',
            'address' => 'nullable|string|max:500',
            'driving_license_number' => 'nullable|string|max:64',
            'driving_license_expiry' => 'nullable|date',
            'internal_notes' => 'nullable|string|max:5000',
        ];
        if ($request->hasFile('cin_front')) {
            $rules['cin_front'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        if ($request->hasFile('cin_back')) {
            $rules['cin_back'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        if ($request->hasFile('license_document')) {
            $rules['license_document'] = 'file|mimes:pdf,jpg,jpeg,png|max:10240';
        }
        return $request->validate($rules);
    }

    private function handleUploads(Request $request, Customer $customer, Company $company): void
    {
        $disk = Storage::disk('customer_documents');
        $baseDir = $company->id . '/customers/' . $customer->id;
        if (! $disk->exists($baseDir)) {
            $disk->makeDirectory($baseDir);
        }
        $updates = [];
        foreach (['cin_front' => 'cin_front_path', 'cin_back' => 'cin_back_path', 'license_document' => 'license_document_path'] as $input => $field) {
            if ($request->hasFile($input)) {
                $file = $request->file($input);
                $path = $baseDir . '/' . $input . '_' . time() . '.' . $file->getClientOriginalExtension();
                $disk->put($path, $file->get());
                $updates[$field] = $path;
            }
        }
        if (! empty($updates)) {
            $customer->update($updates);
        }
    }

    /**
     * Move temp documents (from extraction flow) into customer folder and clear session.
     */
    private function moveTempDocumentsToCustomer(Request $request, Company $company, Customer $customer): void
    {
        $sessionKey = 'customer_create_temp.' . $company->id;
        $data = session()->get($sessionKey);
        if (! $data || empty($data['paths'])) {
            return;
        }

        $disk = Storage::disk('customer_documents');
        $baseDir = $company->id . '/customers/' . $customer->id;
        if (! $disk->exists($baseDir)) {
            $disk->makeDirectory($baseDir);
        }
        $updates = [];
        $typeToField = ['cin_front' => 'cin_front_path', 'cin_back' => 'cin_back_path', 'license' => 'license_document_path'];

        foreach ($data['paths'] as $type => $tempPath) {
            if (! $disk->exists($tempPath)) {
                continue;
            }
            $ext = pathinfo($tempPath, PATHINFO_EXTENSION);
            $newPath = $baseDir . '/' . $type . '_' . time() . '.' . $ext;
            $disk->move($tempPath, $newPath);
            if (isset($typeToField[$type])) {
                $updates[$typeToField[$type]] = $newPath;
            }
        }

        if (! empty($updates)) {
            $customer->update($updates);
        }
        session()->forget($sessionKey);
    }
}
