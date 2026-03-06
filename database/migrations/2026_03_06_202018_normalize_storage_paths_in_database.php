<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize storage paths in DB: strip leading slash and "storage/" prefix
     * so all paths are relative (e.g. "vehicles/1/photo.jpg") for storage_public_url().
     */
    public function up(): void
    {
        $normalize = function (?string $path): ?string {
            if ($path === null || trim($path) === '') {
                return null;
            }
            $path = trim($path);
            $path = ltrim($path, '/');
            if (str_starts_with($path, 'storage/')) {
                $path = substr($path, 8);
            }
            return $path === '' ? null : $path;
        };

        foreach (['vehicles' => ['image_path', 'insurance_document_path', 'vignette_receipt_path', 'visite_document_path', 'financing_contract_path']] as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($columns as $col) {
                if (! Schema::hasColumn($table, $col)) {
                    continue;
                }
                $rows = DB::table($table)->whereNotNull($col)->where($col, '!=', '')->get(['id', $col]);
                foreach ($rows as $row) {
                    $newPath = $normalize($row->$col);
                    if ($newPath !== null && $newPath !== $row->$col) {
                        DB::table($table)->where('id', $row->id)->update([$col => $newPath]);
                    }
                }
            }
        }

        if (Schema::hasTable('reservation_payments') && Schema::hasColumn('reservation_payments', 'receipt_path')) {
            $rows = DB::table('reservation_payments')->whereNotNull('receipt_path')->where('receipt_path', '!=', '')->get(['id', 'receipt_path']);
            foreach ($rows as $row) {
                $newPath = $normalize($row->receipt_path);
                if ($newPath !== null && $newPath !== $row->receipt_path) {
                    DB::table('reservation_payments')->where('id', $row->id)->update(['receipt_path' => $newPath]);
                }
            }
        }

        if (Schema::hasTable('reservation_inspection_photos') && Schema::hasColumn('reservation_inspection_photos', 'path')) {
            $rows = DB::table('reservation_inspection_photos')->get(['id', 'path']);
            foreach ($rows as $row) {
                $newPath = $normalize($row->path);
                if ($newPath !== null && $newPath !== $row->path) {
                    DB::table('reservation_inspection_photos')->where('id', $row->id)->update(['path' => $newPath]);
                }
            }
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'attachment_path')) {
            $rows = DB::table('expenses')->whereNotNull('attachment_path')->where('attachment_path', '!=', '')->get(['id', 'attachment_path']);
            foreach ($rows as $row) {
                $newPath = $normalize($row->attachment_path);
                if ($newPath !== null && $newPath !== $row->attachment_path) {
                    DB::table('expenses')->where('id', $row->id)->update(['attachment_path' => $newPath]);
                }
            }
        }
    }

    public function down(): void
    {
        // No reversible change; paths remain normalized.
    }
};
