<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    public const COMPLIANCE_OK = 'ok';
    public const COMPLIANCE_EXPIRING = 'expiring_soon';
    public const COMPLIANCE_EXPIRED = 'expired';
    public const COMPLIANCE_MISSING = 'missing';

    public const EXPIRING_DAYS = 30;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_INACTIVE = 'inactive';

    /** Vehicle categories (for display and partner availability). */
    public const PARTNER_CATEGORY_ECONOMY = 'economy';
    public const PARTNER_CATEGORY_SEDAN = 'sedan';
    public const PARTNER_CATEGORY_SUV = 'suv';
    public const PARTNER_CATEGORY_MINI = 'mini';
    public const PARTNER_CATEGORY_COMPACTE = 'compacte';
    public const PARTNER_CATEGORY_PREMIUM = 'premium';
    public const PARTNER_CATEGORY_FAMILIALE = 'familiale';
    public const PARTNER_CATEGORY_UTILITAIRE = 'utilitaire';

    public const PARTNER_CATEGORIES = [
        self::PARTNER_CATEGORY_ECONOMY => 'Économique (Clio ou similaire)',
        self::PARTNER_CATEGORY_SEDAN => 'Berline (Mégane ou similaire)',
        self::PARTNER_CATEGORY_SUV => 'SUV (Kadjar ou similaire)',
        self::PARTNER_CATEGORY_MINI => 'Mini (Kia Picanto ou similaire)',
        self::PARTNER_CATEGORY_COMPACTE => 'Compacte (Peugeot 208 ou similaire)',
        self::PARTNER_CATEGORY_PREMIUM => 'Premium (BMW Série 3 ou similaire)',
        self::PARTNER_CATEGORY_FAMILIALE => '7 places / Familiale (Dacia Lodgy ou similaire)',
        self::PARTNER_CATEGORY_UTILITAIRE => 'Utilitaire (Kangoo ou similaire)',
    ];

    protected $fillable = [
        'branch_id',
        'status',
        'plate',
        'brand',
        'model',
        'image_path',
        'partner_category',
        'year',
        'vin',
        'fuel',
        'transmission',
        'mileage',
        'color',
        'seats',
        'daily_price',
        'weekly_price',
        'monthly_price',
        'deposit',
        'insurance_company',
        'insurance_policy_number',
        'insurance_type',
        'insurance_start_date',
        'insurance_end_date',
        'insurance_annual_cost',
        'insurance_document_path',
        'insurance_reminder',
        'vignette_year',
        'vignette_amount',
        'vignette_paid_date',
        'vignette_receipt_path',
        'vignette_reminder',
        'visite_last_date',
        'visite_expiry_date',
        'visite_document_path',
        'visite_reminder',
        'is_financed',
        'financing_type',
        'financing_bank',
        'financing_monthly_payment',
        'financing_start_date',
        'financing_end_date',
        'financing_remaining_amount',
        'financing_contract_path',
    ];

    protected $casts = [
        'year' => 'integer',
        'mileage' => 'integer',
        'seats' => 'integer',
        'daily_price' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'insurance_start_date' => 'date',
        'insurance_end_date' => 'date',
        'insurance_annual_cost' => 'decimal:2',
        'insurance_reminder' => 'boolean',
        'vignette_year' => 'integer',
        'vignette_amount' => 'decimal:2',
        'vignette_paid_date' => 'date',
        'vignette_reminder' => 'boolean',
        'visite_last_date' => 'date',
        'visite_expiry_date' => 'date',
        'visite_reminder' => 'boolean',
        'is_financed' => 'boolean',
        'financing_monthly_payment' => 'decimal:2',
        'financing_start_date' => 'date',
        'financing_end_date' => 'date',
        'financing_remaining_amount' => 'decimal:2',
    ];

    /** Section keys for completion checklist (photo, pricing, insurance, vignette, visite, financing). */
    public const SECTION_PHOTO = 'photo';
    public const SECTION_PRICING = 'pricing';
    public const SECTION_INSURANCE = 'insurance';
    public const SECTION_VIGNETTE = 'vignette';
    public const SECTION_VISITE = 'visite';
    public const SECTION_FINANCING = 'financing';

    /**
     * Whether the vehicle has incomplete information (missing one or more sections).
     */
    public function isIncomplete(): bool
    {
        return ! $this->isComplete();
    }

    /**
     * Whether all 6 sections are considered filled (for badge and progress).
     */
    public function isComplete(): bool
    {
        $checklist = $this->completionChecklist();
        foreach ($checklist as $item) {
            if (! $item['done']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Completion checklist for dashboard and vehicle show (6 sections).
     * Each item: key, label, done, anchor (for edit page hash).
     */
    public function completionChecklist(): array
    {
        $donePhoto = ! empty($this->image_path);
        $donePricing = $this->daily_price !== null && (float) $this->daily_price > 0
            || $this->weekly_price !== null && (float) $this->weekly_price > 0
            || $this->monthly_price !== null && (float) $this->monthly_price > 0;
        $doneInsurance = $this->insurance_end_date !== null || ! empty($this->insurance_document_path);
        $doneVignette = $this->vignette_year !== null;
        $doneVisite = $this->visite_expiry_date !== null || ! empty($this->visite_document_path);
        $doneFinancing = ! $this->is_financed
            || ! empty($this->financing_type)
            || ! empty($this->financing_bank)
            || ! empty($this->financing_contract_path);

        return [
            ['key' => self::SECTION_PHOTO, 'label' => 'Photo', 'done' => $donePhoto, 'anchor' => 'section-photo'],
            ['key' => self::SECTION_PRICING, 'label' => 'Tarification', 'done' => $donePricing, 'anchor' => 'section-pricing'],
            ['key' => self::SECTION_INSURANCE, 'label' => 'Assurance', 'done' => $doneInsurance, 'anchor' => 'section-insurance'],
            ['key' => self::SECTION_VIGNETTE, 'label' => 'Vignette', 'done' => $doneVignette, 'anchor' => 'section-vignette'],
            ['key' => self::SECTION_VISITE, 'label' => 'Visite technique', 'done' => $doneVisite, 'anchor' => 'section-visite'],
            ['key' => self::SECTION_FINANCING, 'label' => 'Financement', 'done' => $doneFinancing, 'anchor' => 'section-financing'],
        ];
    }

    /**
     * Number of completed sections (0–6).
     */
    public function completionCount(): int
    {
        $n = 0;
        foreach ($this->completionChecklist() as $item) {
            if ($item['done']) {
                $n++;
            }
        }
        return $n;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeExpiringSoon($query)
    {
        $threshold = now()->addDays(self::EXPIRING_DAYS)->format('Y-m-d');
        return $query->where(function ($q) use ($threshold) {
            $q->where(function ($q) use ($threshold) {
                $q->whereNotNull('insurance_end_date')->where('insurance_end_date', '<=', $threshold);
            })->orWhere(function ($q) use ($threshold) {
                $q->whereNotNull('visite_expiry_date')->where('visite_expiry_date', '<=', $threshold);
            })->orWhereNotNull('vignette_year')->whereRaw('CONCAT(vignette_year, "-12-31") <= ?', [$threshold]);
        });
    }

    protected function complianceStatus(?Carbon $expiryDate, bool $reminderEnabled): string
    {
        if (! $expiryDate) {
            return self::COMPLIANCE_MISSING;
        }
        if ($expiryDate->isPast()) {
            return self::COMPLIANCE_EXPIRED;
        }
        if ($reminderEnabled && $expiryDate->lte(now()->addDays(self::EXPIRING_DAYS))) {
            return self::COMPLIANCE_EXPIRING;
        }
        return self::COMPLIANCE_OK;
    }

    public function insuranceStatus(): string
    {
        return $this->complianceStatus($this->insurance_end_date, $this->insurance_reminder);
    }

    public function vignetteStatus(): string
    {
        $expiry = $this->vignette_year ? Carbon::createFromFormat('Y', (string) $this->vignette_year)->endOfYear() : null;
        return $this->complianceStatus($expiry, $this->vignette_reminder);
    }

    public function visiteStatus(): string
    {
        return $this->complianceStatus($this->visite_expiry_date, $this->visite_reminder);
    }

    public function hasAnyExpiringSoon(): bool
    {
        return in_array($this->insuranceStatus(), [self::COMPLIANCE_EXPIRING, self::COMPLIANCE_EXPIRED], true)
            || in_array($this->vignetteStatus(), [self::COMPLIANCE_EXPIRING, self::COMPLIANCE_EXPIRED], true)
            || in_array($this->visiteStatus(), [self::COMPLIANCE_EXPIRING, self::COMPLIANCE_EXPIRED], true);
    }

    /** Alert timeline: list of { type, label, date, status } for display. */
    public function complianceAlertTimeline(): array
    {
        $items = [];

        if ($this->insurance_end_date) {
            $items[] = [
                'type' => 'insurance',
                'label' => 'Assurance',
                'date' => $this->insurance_end_date,
                'status' => $this->insuranceStatus(),
            ];
        }
        if ($this->vignette_year) {
            $expiry = Carbon::createFromFormat('Y', (string) $this->vignette_year)->endOfYear();
            $items[] = [
                'type' => 'vignette',
                'label' => 'Vignette',
                'date' => $expiry,
                'status' => $this->vignetteStatus(),
            ];
        }
        if ($this->visite_expiry_date) {
            $items[] = [
                'type' => 'visite',
                'label' => 'Visite technique',
                'date' => $this->visite_expiry_date,
                'status' => $this->visiteStatus(),
            ];
        }

        usort($items, fn ($a, $b) => $a['date']->getTimestamp() <=> $b['date']->getTimestamp());

        return $items;
    }

    public function storeDocument(string $field, UploadedFile $file): string
    {
        $path = $file->store('vehicles/' . $this->id, 'public');
        $this->update([$field => $path]);
        return $path;
    }

    /** Public URL for the vehicle photo (for use in views). */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }
        return asset('storage/' . $this->image_path);
    }

    /** Completion sections for "quick create" flow: key => label. */
    public static function completionSectionLabels(): array
    {
        return [
            'photo' => 'Photo',
            'tarification' => 'Tarification',
            'assurance' => 'Assurance',
            'vignette' => 'Vignette',
            'visite' => 'Visite technique',
            'financement' => 'Financement',
        ];
    }

    public function hasPhoto(): bool
    {
        return ! empty($this->image_path);
    }

    public function hasPricing(): bool
    {
        return $this->daily_price !== null && (float) $this->daily_price > 0
            || $this->weekly_price !== null && (float) $this->weekly_price > 0
            || $this->monthly_price !== null && (float) $this->monthly_price > 0;
    }

    public function hasInsurance(): bool
    {
        return $this->insurance_end_date !== null;
    }

    public function hasVignette(): bool
    {
        return $this->vignette_year !== null;
    }

    public function hasVisite(): bool
    {
        return $this->visite_expiry_date !== null;
    }

    public function hasFinancing(): bool
    {
        if (! $this->is_financed) {
            return true;
        }
        return $this->financing_type !== null || $this->financing_bank !== null
            || ($this->financing_monthly_payment !== null && (float) $this->financing_monthly_payment > 0);
    }

}
