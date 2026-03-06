<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ContractTemplate;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationContract;
use App\Models\ReservationInspection;
use App\Models\ReservationInspectionPhoto;
use App\Models\ReservationPayment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyReservationController extends Controller
{
    private function ensureReservationBelongsToCompany(Reservation $reservation, Company $company): void
    {
        if ($reservation->company_id != $company->id) {
            abort(404);
        }
    }

    private function ensureVehicleBelongsToCompany(Vehicle $vehicle, Company $company): void
    {
        if ($vehicle->branch->company_id != $company->id) {
            abort(404);
        }
    }

    public function index(Request $request, Company $company): View
    {
        $query = $company->reservations()->with(['vehicle', 'customer']);

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('cin', 'like', $term)->orWhere('name', 'like', $term)->orWhere('phone', 'like', $term))
                    ->orWhereHas('vehicle', fn ($v) => $v->where('plate', 'like', $term));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->where('end_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('start_at', '<=', $request->date_to . ' 23:59:59');
        }
        if ($request->filled('branch_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('pickup_branch_id', $request->branch_id)
                    ->orWhere('return_branch_id', $request->branch_id)
                    ->orWhereHas('vehicle', fn ($v) => $v->where('branch_id', $request->branch_id));
            });
        }

        $reservations = $query->orderByDesc('start_at')->paginate(20)->withQueryString();
        $vehicles = $company->vehicles()->orderBy('vehicles.plate')->get(['vehicles.id', 'vehicles.plate', 'vehicles.brand', 'vehicles.model']);
        $branches = $company->branches()->orderBy('name')->get();

        return view('app.companies.reservations.index', [
            'title' => 'Réservations – ' . $company->name,
            'company' => $company,
            'reservations' => $reservations,
            'vehicles' => $vehicles,
            'branches' => $branches,
        ]);
    }

    public function create(Company $company): View|RedirectResponse
    {
        $vehicles = $company->vehicles()->with('branch')->orderBy('plate')->get();
        $customers = $company->customers()->orderBy('name')->get(['id', 'name', 'cin', 'phone', 'email', 'is_flagged']);
        if ($vehicles->isEmpty()) {
            return redirect()
                ->route('app.companies.reservations.index', $company)
                ->with('info', 'Ajoutez au moins un véhicule pour créer une réservation.');
        }
        return view('app.companies.reservations.create', [
            'title' => 'Nouvelle réservation – ' . $company->name,
            'company' => $company,
            'vehicles' => $vehicles,
            'customers' => $customers,
        ]);
    }

    public function store(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'customer_id' => 'required|exists:customers,id',
            'pickup_branch_id' => 'nullable|exists:branches,id',
            'return_branch_id' => 'nullable|exists:branches,id',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:draft,confirmed',
            'notes' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
            'paid_now' => 'nullable|boolean',
            'deposit_received' => 'nullable|boolean',
            'confirm_and_start' => 'nullable|boolean',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $this->ensureVehicleBelongsToCompany($vehicle, $company);
        $customer = Customer::findOrFail($validated['customer_id']);
        if ($customer->company_id != $company->id) {
            abort(404);
        }
        if (!empty($validated['pickup_branch_id']) && $company->branches()->where('id', $validated['pickup_branch_id'])->doesntExist()) {
            $validated['pickup_branch_id'] = null;
        }
        if (!empty($validated['return_branch_id']) && $company->branches()->where('id', $validated['return_branch_id'])->doesntExist()) {
            $validated['return_branch_id'] = null;
        }

        $startAt = Carbon::parse($validated['start_at']);
        $endAt = Carbon::parse($validated['end_at']);
        if ($validated['status'] === Reservation::STATUS_CONFIRMED) {
            $overlap = $company->reservations()
                ->where('vehicle_id', $vehicle->id)
                ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
                ->where('start_at', '<=', $endAt)
                ->where('end_at', '>=', $startAt)
                ->exists();
            if ($overlap) {
                return back()->withInput()->withErrors(['start_at' => 'Ce véhicule est déjà réservé sur cette période.']);
            }
        }

        $reservation = new Reservation;
        $reservation->company_id = $company->id;
        $reservation->vehicle_id = $validated['vehicle_id'];
        $reservation->customer_id = $validated['customer_id'];
        $reservation->pickup_branch_id = $validated['pickup_branch_id'] ?? $vehicle->branch_id;
        $reservation->return_branch_id = $validated['return_branch_id'] ?? $vehicle->branch_id;
        $reservation->reference = Reservation::generateReference($company);
        $reservation->status = $validated['status'];
        $reservation->payment_status = Reservation::PAYMENT_UNPAID;
        $reservation->start_at = $startAt;
        $reservation->end_at = $endAt;
        $reservation->total_price = $validated['total_price'];
        $reservation->notes = $validated['notes'] ?? null;
        $reservation->internal_notes = $validated['internal_notes'] ?? null;
        if ($validated['status'] === Reservation::STATUS_CONFIRMED) {
            $reservation->confirmed_at = now();
        }
        $reservation->save();

        if ($reservation->status === Reservation::STATUS_CONFIRMED && $request->boolean('confirm_and_start')) {
            $reservation->update(['status' => Reservation::STATUS_IN_PROGRESS, 'started_at' => now()]);
        }

        $branchId = $reservation->pickup_branch_id ?? $reservation->vehicle?->branch_id;
        if ($request->boolean('paid_now') && (float) $reservation->total_price > 0) {
            $reservation->payments()->create([
                'branch_id' => $branchId,
                'amount' => $reservation->total_price,
                'method' => ReservationPayment::METHOD_CASH,
                'type' => ReservationPayment::TYPE_RENTAL,
                'paid_at' => now()->toDateString(),
                'reference' => 'Paiement à la confirmation',
            ]);
        }
        if ($request->boolean('deposit_received') && (float) $vehicle->deposit > 0) {
            $reservation->payments()->create([
                'branch_id' => $branchId,
                'amount' => $vehicle->deposit,
                'method' => ReservationPayment::METHOD_CASH,
                'type' => ReservationPayment::TYPE_DEPOSIT,
                'paid_at' => now()->toDateString(),
                'reference' => 'Caution reçue',
            ]);
        }
        if ($reservation->payments()->exists()) {
            $reservation->refreshPaymentStatus();
        }

        $reservation->load(['customer', 'vehicle']);
        $reservationUrl = route('app.companies.reservations.show', [$company, $reservation]);
        $company->users()->where('role', 'company_admin')->get()->each(function ($u) use ($reservation, $reservationUrl) {
            $u->notify(new \App\Notifications\ReservationCreatedNotification($reservation, $reservationUrl));
        });

        $message = $reservation->status === Reservation::STATUS_IN_PROGRESS ? 'Réservation confirmée et location démarrée.'
            : ($reservation->status === Reservation::STATUS_CONFIRMED ? 'Réservation confirmée.' : 'Brouillon enregistré.');

        return redirect()
            ->route('app.companies.reservations.show', [$company, $reservation])
            ->with('success', $message);
    }

    public function show(Company $company, Reservation $reservation): View
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $reservation->load(['vehicle.branch', 'pickupBranch', 'returnBranch', 'customer', 'reservationContract', 'inspections.photos', 'inspectionOut.photos', 'inspectionIn.photos', 'payments']);
        $company->load('defaultContractTemplate');
        $contractTemplates = $company->contractTemplates()->orderBy('name')->get();
        $globalTemplates = ContractTemplate::global()->orderBy('name')->get();

        return view('app.companies.reservations.show', [
            'title' => $reservation->reference . ' – ' . $company->name,
            'company' => $company,
            'reservation' => $reservation,
            'contractTemplates' => $contractTemplates,
            'globalTemplates' => $globalTemplates,
        ]);
    }

    public function confirm(Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if ($reservation->status !== Reservation::STATUS_DRAFT) {
            return back()->with('error', 'Seul un brouillon peut être confirmé.');
        }
        $overlap = $company->reservations()
            ->where('vehicle_id', $reservation->vehicle_id)
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
            ->where('id', '!=', $reservation->id)
            ->where('start_at', '<=', $reservation->end_at)
            ->where('end_at', '>=', $reservation->start_at)
            ->exists();
        if ($overlap) {
            return back()->with('error', 'Ce véhicule est déjà réservé sur cette période.');
        }
        $reservation->update(['status' => Reservation::STATUS_CONFIRMED, 'confirmed_at' => now()]);
        return back()->with('success', 'Réservation confirmée.');
    }

    public function cancel(Company $company, Reservation $reservation): RedirectResponse
    {
        if (auth()->user()?->role === 'agent') {
            abort(403, 'Seuls les administrateurs peuvent annuler une réservation.');
        }
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if (in_array($reservation->status, [Reservation::STATUS_CANCELLED, Reservation::STATUS_COMPLETED], true)) {
            return back()->with('error', 'Cette réservation ne peut pas être annulée.');
        }
        $reservation->update(['status' => Reservation::STATUS_CANCELLED, 'cancelled_at' => now()]);
        return back()->with('success', 'Réservation annulée.');
    }

    public function markPaid(Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $remaining = $reservation->remaining_amount;
        if ($remaining > 0) {
            $branchId = $reservation->pickup_branch_id ?? $reservation->vehicle?->branch_id;
            $reservation->payments()->create([
                'branch_id' => $branchId,
                'amount' => $remaining,
                'method' => ReservationPayment::METHOD_CASH,
                'type' => ReservationPayment::TYPE_RENTAL,
                'paid_at' => now()->toDateString(),
                'reference' => 'Solde',
            ]);
            $reservation->refreshPaymentStatus();
        } else {
            $reservation->update(['payment_status' => Reservation::PAYMENT_PAID]);
        }
        return back()->with('success', 'Paiement enregistré.');
    }

    public function storePayment(Request $request, Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:cash,virement,TPE,cheque',
            'type' => 'required|string|in:deposit,rental,fee,refund',
            'paid_at' => 'required|date',
            'reference' => 'nullable|string|max:128',
            'note' => 'nullable|string|max:2000',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('payments/' . $reservation->id, 'public');
        }
        $branchId = $reservation->pickup_branch_id ?? $reservation->vehicle?->branch_id;
        $reservation->payments()->create([
            'branch_id' => $branchId,
            'amount' => $validated['amount'],
            'method' => $validated['method'],
            'type' => $validated['type'],
            'paid_at' => $validated['paid_at'],
            'reference' => $validated['reference'] ?? null,
            'note' => $validated['note'] ?? null,
            'receipt_path' => $receiptPath,
        ]);
        $reservation->refreshPaymentStatus();
        return back()->with('success', 'Paiement enregistré.');
    }

    public function refundDeposit(Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $depositExpected = $reservation->deposit_expected;
        if ($depositExpected <= 0) {
            return back()->with('error', 'Aucune caution définie pour ce véhicule.');
        }
        $branchId = $reservation->return_branch_id ?? $reservation->pickup_branch_id ?? $reservation->vehicle?->branch_id;
        $reservation->payments()->create([
            'branch_id' => $branchId,
            'amount' => $depositExpected,
            'method' => ReservationPayment::METHOD_VIREMENT,
            'type' => ReservationPayment::TYPE_REFUND,
            'paid_at' => now()->toDateString(),
            'reference' => 'Remboursement caution',
            'note' => 'Remboursement caution état des lieux retour.',
        ]);
        $reservation->refreshPaymentStatus();
        return back()->with('success', 'Remboursement caution enregistré.');
    }

    public function receipt(Company $company, Reservation $reservation): View
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $reservation->load(['company', 'customer', 'vehicle.branch', 'payments']);
        return view('app.companies.reservations.receipt', [
            'company' => $company,
            'reservation' => $reservation,
        ]);
    }

    public function startRental(Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if ($reservation->status !== Reservation::STATUS_CONFIRMED) {
            return back()->with('error', 'Seule une réservation confirmée peut être démarrée.');
        }
        $reservation->update(['status' => Reservation::STATUS_IN_PROGRESS, 'started_at' => now()]);
        return back()->with('success', 'Location démarrée.');
    }

    public function completeRental(Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if ($reservation->status !== Reservation::STATUS_IN_PROGRESS) {
            return back()->with('error', 'Seule une location en cours peut être clôturée.');
        }
        $reservation->update(['status' => Reservation::STATUS_COMPLETED, 'completed_at' => now()]);
        return back()->with('success', 'Location terminée.');
    }

    /** JSON: reserved date ranges for the vehicle (for calendar). */
    public function vehicleAvailability(Company $company, Vehicle $vehicle): JsonResponse
    {
        $this->ensureVehicleBelongsToCompany($vehicle, $company);
        $ranges = $company->reservations()
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_IN_PROGRESS])
            ->get(['start_at', 'end_at'])
            ->map(fn ($r) => [
                'start' => $r->start_at->format('Y-m-d'),
                'end' => $r->end_at->format('Y-m-d'),
            ]);
        return response()->json(['reserved' => $ranges]);
    }

    /** Resolve template: must be company's or global. */
    private function getContractTemplateForCompany(?int $templateId, Company $company): ?ContractTemplate
    {
        if (!$templateId) {
            return $company->defaultContractTemplate;
        }
        $template = ContractTemplate::find($templateId);
        if (!$template) {
            return null;
        }
        if ($template->company_id !== null && $template->company_id != $company->id) {
            return null;
        }
        return $template;
    }

    public function contractPreview(Request $request, Company $company, Reservation $reservation)
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $company->load('defaultContractTemplate');
        $templateId = $request->query('template_id');
        $template = $this->getContractTemplateForCompany($templateId !== null && $templateId !== '' ? (int) $templateId : null, $company);
        if (! $template) {
            return response('<p class="p-4 text-slate-400">Sélectionnez un modèle de contrat ou définissez un modèle par défaut pour l’entreprise.</p>', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
        }
        $html = $template->renderForReservation($reservation);
        return response()->view('app.companies.reservations.contract-preview-html', [
            'content' => $html,
            'company' => $company,
            'reservation' => $reservation,
        ], 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function contractGenerate(Request $request, Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $templateId = $request->input('template_id');
        $template = $this->getContractTemplateForCompany($templateId ? (int) $templateId : null, $company);
        if (!$template) {
            return back()->with('error', 'Modèle de contrat invalide ou manquant.');
        }
        $html = $template->renderForReservation($reservation);
        $contract = $reservation->reservationContract;
        if (!$contract) {
            $contract = new ReservationContract(['reservation_id' => $reservation->id]);
        }
        $contract->contract_template_id = $template->id;
        $contract->snapshot_html = $html;
        $contract->status = ReservationContract::STATUS_GENERATED;
        $contract->generated_at = now();
        $contract->save();
        $reservation->update(['contract_status' => Reservation::CONTRACT_STATUS_GENERATED]);
        $reservation->load(['customer', 'vehicle']);
        $reservationUrl = route('app.companies.reservations.show', [$company, $reservation]);
        $company->users()->where('role', 'company_admin')->get()->each(function ($u) use ($reservation, $reservationUrl) {
            $u->notify(new \App\Notifications\ContractGeneratedNotification($reservation, $reservationUrl));
        });
        return back()->with('success', 'Contrat généré. Vous pouvez l’imprimer ou l’exporter en PDF.');
    }

    public function contractPrint(Company $company, Reservation $reservation): View
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $contract = $reservation->reservationContract;
        $html = $contract && $contract->snapshot_html
            ? $contract->snapshot_html
            : null;
        if ($html === null) {
            $company->load('defaultContractTemplate');
            $template = $this->getContractTemplateForCompany(null, $company);
            $html = $template ? $template->renderForReservation($reservation) : '<p>Aucun contrat généré et aucun modèle par défaut.</p>';
        }
        return view('app.companies.reservations.contract-print', [
            'company' => $company,
            'reservation' => $reservation,
            'content' => $html,
        ]);
    }

    public function storeInspection(Request $request, Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        $type = $request->input('type') === ReservationInspection::TYPE_IN ? ReservationInspection::TYPE_IN : ReservationInspection::TYPE_OUT;

        $rules = [
            'inspected_at' => 'nullable|date',
            'mileage' => 'nullable|integer|min:0',
            'fuel_level' => 'nullable|string|in:vide,1/4,1/2,3/4,plein',
            'notes' => 'nullable|string|max:5000',
            'photos' => 'nullable|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:10240',
        ];
        if ($type === ReservationInspection::TYPE_OUT) {
            $rules['damage_checklist'] = 'nullable|array';
            $rules['damage_checklist.*.area'] = 'nullable|string|max:255';
            $rules['damage_checklist.*.description'] = 'nullable|string|max:500';
        } else {
            $rules['new_damages'] = 'nullable|string|max:5000';
            $rules['extra_fees'] = 'nullable|numeric|min:0';
            $rules['deposit_refund_status'] = 'nullable|string|in:pending,refunded,retained,partial';
        }
        $validated = $request->validate($rules);

        $inspection = $reservation->inspections()->firstOrNew(['type' => $type], ['type' => $type]);
        $inspection->inspected_at = $validated['inspected_at'] ? \Carbon\Carbon::parse($validated['inspected_at']) : null;
        $inspection->mileage = $validated['mileage'] ?? null;
        $inspection->fuel_level = $validated['fuel_level'] ?? null;
        $inspection->notes = $validated['notes'] ?? null;
        if ($type === ReservationInspection::TYPE_OUT) {
            $list = $validated['damage_checklist'] ?? [];
            if (is_string($list)) {
                $list = json_decode($list, true) ?: [];
            }
            $inspection->damage_checklist = !empty($list) ? array_values(array_filter($list, fn ($d) => !empty($d['area'] ?? null) || !empty($d['description'] ?? null))) : null;
        } else {
            $inspection->new_damages = $validated['new_damages'] ?? null;
            $inspection->extra_fees = $validated['extra_fees'] ?? null;
            $inspection->deposit_refund_status = $validated['deposit_refund_status'] ?? null;
        }
        $inspection->save();

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('inspections/' . $inspection->id, 'public');
                $inspection->photos()->create(['path' => $path]);
            }
        }
        return back()->with('success', $type === ReservationInspection::TYPE_OUT ? 'État des lieux (départ) enregistré.' : 'État des lieux (retour) enregistré.');
    }

    /**
     * Upload signed contract (PDF). Private disk. Replace only if replace_confirm=1 when one already exists.
     */
    public function storeSignedContract(Request $request, Company $company, Reservation $reservation): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if ($reservation->contract_signed_path && ! $request->boolean('replace_confirm')) {
            return back()->with('error', 'Un contrat signé existe déjà. Veuillez confirmer le remplacement.');
        }
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf|max:10240', // 10MB
            'contract_signed_notes' => 'nullable|string|max:5000',
        ]);
        $file = $request->file('signed_pdf');
        $dir = 'contracts/signed/' . $company->id;
        $filename = $reservation->id . '-' . now()->timestamp . '.pdf';
        $path = $file->storeAs($dir, $filename, 'local');
        if ($reservation->contract_signed_path && Storage::disk('local')->exists($reservation->contract_signed_path)) {
            Storage::disk('local')->delete($reservation->contract_signed_path);
        }
        $reservation->update([
            'contract_signed_path' => $path,
            'contract_signed_at' => now(),
            'contract_signed_notes' => $request->input('contract_signed_notes'),
            'contract_status' => Reservation::CONTRACT_STATUS_SIGNED,
        ]);
        $reservation->reservationContract?->update(['status' => ReservationContract::STATUS_SIGNED]);

        $reservation->load(['customer', 'vehicle']);
        $reservationUrl = route('app.companies.reservations.show', [$company, $reservation]);
        $company->users()->where('role', 'company_admin')->get()->each(function ($u) use ($reservation, $reservationUrl) {
            $u->notify(new \App\Notifications\ContractSignedNotification($reservation, $reservationUrl));
        });

        return back()->with('success', 'Contrat signé enregistré.');
    }

    /**
     * Download signed contract PDF (private storage, authorized only).
     */
    public function downloadSignedContract(Company $company, Reservation $reservation): Response|RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if (! $reservation->contract_signed_path) {
            return back()->with('error', 'Aucun contrat signé.');
        }
        if (! Storage::disk('local')->exists($reservation->contract_signed_path)) {
            return back()->with('error', 'Fichier introuvable.');
        }
        $content = Storage::disk('local')->get($reservation->contract_signed_path);
        $name = 'contrat-signe-' . $reservation->reference . '.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }

    public function deleteInspectionPhoto(Company $company, Reservation $reservation, ReservationInspectionPhoto $photo): RedirectResponse
    {
        $this->ensureReservationBelongsToCompany($reservation, $company);
        if ($photo->reservationInspection->reservation_id != $reservation->id) {
            abort(404);
        }
        $photo->delete();
        return back()->with('success', 'Photo supprimée.');
    }
}
