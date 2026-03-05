<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerRequest extends Model
{
    protected $table = 'partner_requests';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'requester_company_id',
        'partner_company_id',
        'branch_id',
        'category',
        'from_date',
        'to_date',
        'message',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'responded_at' => 'datetime',
    ];

    public function requesterCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'requester_company_id');
    }

    public function partnerCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'partner_company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
