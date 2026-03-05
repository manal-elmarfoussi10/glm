<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Generate and stream a CSV file.
     */
    public static function streamCsv(string $filename, array $headers, iterable $records, callable $rowCallback): StreamedResponse
    {
        return Response::streamDownload(function () use ($headers, $records, $rowCallback) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility with UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $headers, ';');

            foreach ($records as $record) {
                fputcsv($file, $rowCallback($record), ';');
            }

            fclose($file);
        }, $filename . '_' . now()->format('Y-m-d_H-i') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
