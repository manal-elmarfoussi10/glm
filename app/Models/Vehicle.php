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

    /** Partner availability categories (Economy / Sedan / SUV or similar). */
    public const PARTNER_CATEGORY_ECONOMY = 'economy';
    public const PARTNER_CATEGORY_SEDAN = 'sedan';
    public const PARTNER_CATEGORY_SUV = 'suv';

    public const PARTNER_CATEGORIES = [
        self::PARTNER_CATEGORY_ECONOMY => 'Économique (Clio ou similaire)',
        self::PARTNER_CATEGORY_SEDAN => 'Berline (Mégane ou similaire)',
        self::PARTNER_CATEGORY_SUV => 'SUV (Kadjar ou similaire)',
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
                'label' => 'Vignette (Dariba)',
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
}
