<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'customer_id',
        'pickup_branch_id',
        'return_branch_id',
        'reference',
        'status',
        'payment_status',
        'start_at',
        'end_at',
        'total_price',
        'notes',
        'internal_notes',
        'contract_status',
        'contract_generated_path',
        'contract_signed_path',
        'contract_signed_at',
        'contract_signed_notes',
        'confirmed_at',
        'cancelled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'total_price' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'contract_signed_at' => 'datetime',
    ];

    public const CONTRACT_STATUS_DRAFT = 'draft';
    public const CONTRACT_STATUS_GENERATED = 'generated';
    public const CONTRACT_STATUS_SIGNED = 'signed';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function pickupBranch()
    {
        return $this->belongsTo(Branch::class, 'pickup_branch_id');
    }

    public function returnBranch()
    {
        return $this->belongsTo(Branch::class, 'return_branch_id');
    }

    public function reservationContract()
    {
        return $this->hasOne(ReservationContract::class);
    }

    public function payments()
    {
        return $this->hasMany(ReservationPayment::class);
    }

    /** Total amount paid (incoming payments minus refunds). */
    public function getPaidAmountAttribute(): float
    {
        if (!isset($this->relations['payments'])) {
            $this->load('payments');
        }
        return (float) $this->payments->sum(fn ($p) => $p->signedAmount());
    }

    /** Expected deposit from vehicle. */
    public function getDepositExpectedAttribute(): float
    {
        $this->loadMissing('vehicle');
        return (float) ($this->vehicle->deposit ?? 0);
    }

    /** Total to collect = rental + deposit. */
    public function getTotalDueAttribute(): float
    {
        return (float) $this->total_price + $this->deposit_expected;
    }

    /** Remaining to pay (total due - paid). */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->total_due - $this->paid_amount);
    }

    /** Recompute and persist payment_status. */
    public function refreshPaymentStatus(): void
    {
        $paid = $this->paid_amount;
        $due = $this->total_due;
        if ($due <= 0) {
            $status = self::PAYMENT_PAID;
        } elseif ($paid >= $due) {
            $status = self::PAYMENT_PAID;
        } elseif ($paid > 0) {
            $status = self::PAYMENT_PARTIAL;
        } else {
            $status = self::PAYMENT_UNPAID;
        }
        $this->update(['payment_status' => $status]);
    }

    public function inspections()
    {
        return $this->hasMany(ReservationInspection::class);
    }

    public function inspectionOut()
    {
        return $this->hasOne(ReservationInspection::class)->where('type', ReservationInspection::TYPE_OUT);
    }

    public function inspectionIn()
    {
        return $this->hasOne(ReservationInspection::class)->where('type', ReservationInspection::TYPE_IN);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('end_at', '>=', $from);
        }
        if ($to) {
            $query->where('start_at', '<=', $to);
        }
        return $query;
    }

    /** Number of days (at least 1) for pricing. */
    public function getDaysAttribute(): int
    {
        $days = $this->start_at->diffInDays($this->end_at);
        return max(1, (int) $days);
    }

    public static function generateReference(Company $company): string
    {
        $prefix = 'RES-' . now()->format('Ymd');
        $last = static::where('company_id', $company->id)
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');
        if (!$last) {
            return $prefix . '-001';
        }
        $num = (int) substr($last, -3);
        return $prefix . '-' . str_pad((string) ($num + 1), 3, '0', STR_PAD_LEFT);
    }
}
