<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPartnerSetting extends Model
{
    protected $table = 'company_partner_settings';

    protected $fillable = [
        'company_id',
        'share_enabled',
        'shared_branch_ids',
        'shared_categories',
        'show_price',
        'allow_contact_requests',
    ];

    protected $casts = [
        'share_enabled' => 'boolean',
        'shared_branch_ids' => 'array',
        'shared_categories' => 'array',
        'show_price' => 'boolean',
        'allow_contact_requests' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getSharedBranchesAttribute()
    {
        $ids = $this->shared_branch_ids ?? [];
        if (empty($ids)) {
            return collect();
        }
        return Branch::whereIn('id', $ids)->where('company_id', $this->company_id)->get();
    }
}
