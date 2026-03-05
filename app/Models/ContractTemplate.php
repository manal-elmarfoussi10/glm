<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'source_global_id',
        'name',
        'slug',
        'content',
        'variables',
        'version',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sourceGlobal()
    {
        return $this->belongsTo(ContractTemplate::class, 'source_global_id');
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function isGlobal(): bool
    {
        return is_null($this->company_id);
    }

    /**
     * Render template content with reservation data (for contract generation).
     */
    public function renderForReservation(Reservation $reservation): string
    {
        return app(\App\Services\ContractRenderer::class)->render($this->content ?? '', $reservation);
    }

    /**
     * Preview content with sample data (for template editor).
     */
    public function contentForPreview(): string
    {
        return app(\App\Services\ContractRenderer::class)->renderWithSampleData($this->content ?? '');
    }
}
