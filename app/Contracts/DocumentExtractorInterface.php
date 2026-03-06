<?php

namespace App\Contracts;

/**
 * Extract structured data from identity/driving documents (CIN, permis).
 * V1: assistive extraction; not guaranteed accurate. Implementations can be swapped for better OCR/ML later.
 */
interface DocumentExtractorInterface
{
    /**
     * Extract data from a single file. Type: 'cin_front', 'cin_back', 'license'.
     *
     * @return array{name?: string, cin?: string, address?: string, driving_license_number?: string, driving_license_expiry?: string}
     */
    public function extractFromFile(string $path, string $type): array;

    /**
     * Merge multiple extraction results (e.g. CIN recto + verso + permis) into one set of fields.
     *
     * @param  array<int, array<string, string>>  $results
     * @return array{name?: string, cin?: string, address?: string, driving_license_number?: string, driving_license_expiry?: string}
     */
    public function mergeExtracted(array $results): array;
}
