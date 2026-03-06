<?php

namespace App\Services;

use App\Contracts\DocumentExtractorInterface;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentExtractionService implements DocumentExtractorInterface
{
    public function extractFromFile(string $path, string $type): array
    {
        if (! file_exists($path)) {
            return [];
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $text = $ext === 'pdf' ? $this->extractTextFromPdf($path) : $this->extractTextFromImage($path);
        if ($text === '') {
            return [];
        }
        if (in_array($type, ['cin_front', 'cin_back'], true)) {
            return $this->parseCinLikeText($text);
        }
        if ($type === 'license') {
            return $this->parsePermisLikeText($text);
        }
        return [];
    }

    public function mergeExtracted(array $results): array
    {
        $merged = [];
        $keys = ['name', 'cin', 'address', 'driving_license_number', 'driving_license_expiry'];
        foreach ($keys as $key) {
            foreach ($results as $r) {
                if (! empty($r[$key]) && trim((string) $r[$key]) !== '') {
                    $merged[$key] = trim((string) $r[$key]);
                    break;
                }
            }
        }
        if (isset($merged['driving_license_expiry']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $merged['driving_license_expiry']) === 0) {
            $merged['driving_license_expiry'] = $this->normalizeDate($merged['driving_license_expiry']);
        }
        return $merged;
    }

    private function extractTextFromPdf(string $path): string
    {
        try {
            $parser = new PdfParser;
            $pdf = $parser->parseFile($path);
            return $pdf->getText() ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function extractTextFromImage(string $path): string
    {
        // V1: no image OCR; can be replaced with Tesseract or external API later
        return '';
    }

    private function parseCinLikeText(string $text): array
    {
        $out = [];
        $lines = preg_split('/\r\n|\n|\r/', $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // CIN: Moroccan format often like AB123456 or digits
            if (preg_match('/\b([A-Z]{1,2}\s*\d{5,10})\b/i', $line, $m)) {
                $out['cin'] = preg_replace('/\s+/', '', $m[1]);
            }
            if (preg_match('/\b(\d{7,10})\b/', $line, $m) && ! isset($out['cin'])) {
                $out['cin'] = $m[1];
            }
            // Name: often "Nom" / "Name" followed by value, or a long capitalized line
            if (preg_match('/nom\s*[:.]?\s*(.+)/ui', $line, $m)) {
                $out['name'] = trim($m[1]);
            }
            if (preg_match('/adresse\s*[:.]?\s*(.+)/ui', $line, $m)) {
                $out['address'] = trim($m[1]);
            }
        }
        // Fallback: take a line that looks like a full name (2–4 words, capitalized)
        if (empty($out['name'])) {
            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) >= 6 && strlen($line) <= 80 && preg_match('/^[\p{L}\s\-\']+$/u', $line) && substr_count($line, ' ') >= 1) {
                    $out['name'] = $line;
                    break;
                }
            }
        }
        return $out;
    }

    private function parsePermisLikeText(string $text): array
    {
        $out = [];
        $lines = preg_split('/\r\n|\n|\r/', $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // Permis number: digits or alphanumeric
            if (preg_match('/\b(\d{8,12})\b/', $line, $m)) {
                $out['driving_license_number'] = $m[1];
            }
            if (preg_match('/permis?\s*[:.#]?\s*(\S+)/ui', $line, $m)) {
                $out['driving_license_number'] = trim($m[1]);
            }
            // Expiry date: dd/mm/yyyy or dd-mm-yyyy or yyyy-mm-dd
            if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $line, $m)) {
                $out['driving_license_expiry'] = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            }
            if (preg_match('/\b(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})\b/', $line, $m)) {
                $out['driving_license_expiry'] = sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
            }
            if (preg_match('/expir(?:ation)?\s*[:.]?\s*(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4})/ui', $line, $m)) {
                $d = $m[1];
                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $d, $dm)) {
                    $out['driving_license_expiry'] = sprintf('%04d-%02d-%02d', (int) $dm[3], (int) $dm[2], (int) $dm[1]);
                }
            }
        }
        return $out;
    }

    private function normalizeDate(string $value): string
    {
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', trim($value), $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', trim($value), $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }
        return $value;
    }
}
