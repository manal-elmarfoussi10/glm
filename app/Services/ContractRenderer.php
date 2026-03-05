<?php

namespace App\Services;

use App\Models\Reservation;

class ContractRenderer
{
    /**
     * Replace all placeholders in template HTML with reservation/company/client/vehicle data.
     * Handles {{key}} and {{ key }}.
     */
    public function render(string $templateHtml, Reservation $reservation): string
    {
        $replace = $this->buildReplacementMap($reservation);
        $content = $templateHtml;

        foreach ($replace as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }
        // Remove any remaining placeholders
        $content = preg_replace('/\{\{\s*\w+\s*\}\}/', '', $content);

        return $content;
    }

    /**
     * Build placeholder => value map for a reservation.
     */
    public function buildReplacementMap(Reservation $reservation): array
    {
        $r = $reservation->loadMissing(['company', 'customer', 'vehicle']);
        $company = $r->company;
        $customer = $r->customer;
        $vehicle = $r->vehicle;

        $dailyRate = $vehicle ? (float) ($vehicle->daily_price ?? 0) : 0;
        $deposit = $vehicle ? (float) ($vehicle->deposit ?? 0) : 0;
        $days = $r->days;

        $fullAddress = $customer ? $this->formatAddress($customer->address ?? null, $customer->city ?? null) : '';
        $companyAddress = $company ? $this->formatAddress($company->address ?? null, $company->city ?? null) : '';

        return [
            'client_name' => optional($customer)->name ?? '',
            'client_phone' => optional($customer)->phone ?? '',
            'client_email' => optional($customer)->email ?? '',
            'client_cin' => optional($customer)->cin ?? '',
            'client_address' => $customer ? $fullAddress : '',
            'rental_start_date' => $r->start_at->format('d/m/Y'),
            'rental_end_date' => $r->end_at->format('d/m/Y'),
            'rental_days' => (string) $days,
            'vehicle_name' => $vehicle ? trim(($vehicle->brand ?? '') . ' ' . ($vehicle->model ?? '')) : '',
            'vehicle_plate' => optional($vehicle)->plate ?? '',
            'vehicle_brand' => optional($vehicle)->brand ?? '',
            'vehicle_model' => optional($vehicle)->model ?? '',
            'vehicle_year' => ($vehicle && $vehicle->year) ? (string) $vehicle->year : '',
            'daily_rate' => number_format($dailyRate, 2, ',', ' '),
            'total_amount' => number_format((float) $r->total_price, 2, ',', ' '),
            'deposit_amount' => number_format($deposit, 2, ',', ' '),
            'company_name' => optional($company)->name ?? '',
            'company_ice' => optional($company)->ice ?? '',
            'company_address' => $companyAddress,
            'company_phone' => optional($company)->phone ?? '',
            'company_email' => optional($company)->email ?? '',
            'contract_number' => $r->reference ?? '',
            'contract_date' => now()->format('d/m/Y'),
            'signature_date' => '',
        ];
    }

    private function formatAddress(?string $address, ?string $city): string
    {
        $parts = array_filter([$address, $city]);
        return implode(', ', $parts);
    }

    /**
     * Return sample values for preview (no reservation).
     */
    public function sampleData(): array
    {
        return [
            'client_name' => 'Jean Dupont',
            'client_phone' => '0612345678',
            'client_email' => 'jean.dupont@exemple.ma',
            'client_cin' => 'AB123456',
            'client_address' => '12 Rue Example, Casablanca',
            'rental_start_date' => now()->format('d/m/Y'),
            'rental_end_date' => now()->addDays(3)->format('d/m/Y'),
            'rental_days' => '3',
            'vehicle_name' => 'Renault Clio',
            'vehicle_plate' => '12345-A-1',
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
            'vehicle_year' => '2022',
            'daily_rate' => '250,00',
            'total_amount' => '750,00',
            'deposit_amount' => '2 000,00',
            'company_name' => 'Votre Entreprise SARL',
            'company_ice' => '001234567000089',
            'company_address' => 'Avenue Mohammed V, Casablanca',
            'company_phone' => '0522123456',
            'company_email' => 'contact@entreprise.ma',
            'contract_number' => 'RES-' . now()->format('Ymd') . '-001',
            'contract_date' => now()->format('d/m/Y'),
            'signature_date' => '',
        ];
    }

    /**
     * Render template with sample data (for template preview).
     */
    public function renderWithSampleData(string $templateHtml): string
    {
        $replace = $this->sampleData();
        $content = $templateHtml;
        foreach ($replace as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }
        $content = preg_replace('/\{\{\s*\w+\s*\}\}/', '[?]', $content);
        return $content;
    }
}
