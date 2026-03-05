<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationPayment extends Model
{
    public const METHOD_CASH = 'cash';
    public const METHOD_VIREMENT = 'virement';
    public const METHOD_TPE = 'TPE';
    public const METHOD_CHEQUE = 'cheque';

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_RENTAL = 'rental';
    public const TYPE_FEE = 'fee';
    public const TYPE_REFUND = 'refund';

    protected $fillable = [
        'reservation_id',
        'branch_id',
        'amount',
        'method',
        'type',
        'paid_at',
        'reference',
        'note',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isRefund(): bool
    {
        return $this->type === self::TYPE_REFUND;
    }

    /** Amount to add to "paid" (negative for refund). */
    public function signedAmount(): float
    {
        return $this->isRefund() ? - (float) $this->amount : (float) $this->amount;
    }
}
