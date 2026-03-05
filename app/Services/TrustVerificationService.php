<?php

namespace App\Services;

use App\Models\CompanyClientFlag;
use App\Models\TrustVerification;

class TrustVerificationService
{
    /**
     * Build a stable identifier from client phone/email (anonymized, no PII stored in shared trust).
     */
    public function clientIdentifierFromInput(?string $phone = null, ?string $email = null): string
    {
        $parts = [];
        if ($phone !== null && trim($phone) !== '') {
            $parts[] = 'phone:' . preg_replace('/\D/', '', $phone);
        }
        if ($email !== null && trim($email) !== '') {
            $parts[] = 'email:' . strtolower(trim($email));
        }
        if (empty($parts)) {
            return hash('sha256', 'empty');
        }
        sort($parts);

        return hash('sha256', implode('|', $parts));
    }

    public function getTrustData(string $clientIdentifier): ?TrustVerification
    {
        return TrustVerification::where('client_identifier', $clientIdentifier)->first();
    }

    public function getOrCreateTrust(string $clientIdentifier): TrustVerification
    {
        return TrustVerification::firstOrCreate(
            ['client_identifier' => $clientIdentifier],
            ['verified_identity' => false, 'successful_rentals_count' => 0]
        );
    }

    public function getFlagForCompany(int $companyId, string $clientIdentifier): ?CompanyClientFlag
    {
        return CompanyClientFlag::where('company_id', $companyId)
            ->where('client_identifier', $clientIdentifier)
            ->first();
    }

    public function flagClient(int $companyId, string $clientIdentifier, ?string $reason = null, ?string $notes = null): CompanyClientFlag
    {
        return CompanyClientFlag::updateOrCreate(
            [
                'company_id' => $companyId,
                'client_identifier' => $clientIdentifier,
            ],
            ['reason' => $reason, 'notes' => $notes]
        );
    }

    public function unflagClient(int $companyId, string $clientIdentifier): bool
    {
        return CompanyClientFlag::where('company_id', $companyId)
            ->where('client_identifier', $clientIdentifier)
            ->delete() > 0;
    }

    public function recordSuccessfulRental(string $clientIdentifier): TrustVerification
    {
        $trust = $this->getOrCreateTrust($clientIdentifier);
        $trust->increment('successful_rentals_count');

        return $trust->fresh();
    }

    public function setVerified(string $clientIdentifier, bool $verified = true): TrustVerification
    {
        $trust = $this->getOrCreateTrust($clientIdentifier);
        $trust->update(['verified_identity' => $verified]);

        return $trust->fresh();
    }
}
