<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_public_url')) {
    /**
     * Generate a URL for a file on the public storage disk.
     * Use this for all public assets: vehicle images, vehicle docs, receipts, etc.
     *
     * - Normalizes path: strips leading slash and "storage/" prefix so DB can store clean relative paths.
     * - Returns null if path is empty.
     * - Does not check if file exists; use for building URLs only.
     */
    function storage_public_url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }
        $path = trim($path);
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('storage_public_exists')) {
    /**
     * Check if a file exists on the public storage disk (path normalized same as storage_public_url).
     */
    function storage_public_exists(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }
        $path = trim($path);
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return Storage::disk('public')->exists($path);
    }
}

if (! function_exists('normalize_storage_path')) {
    /**
     * Normalize a path for storage in DB (public disk): no leading slash, no "storage/" prefix.
     * Use when saving paths to DB so we store only relative paths like "vehicles/1/photo.jpg".
     */
    function normalize_storage_path(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }
        $path = trim($path);
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return $path === '' ? null : $path;
    }
}

if (! function_exists('app_asset')) {
    /**
     * URL for a static asset in public/ (e.g. images/light-logo.png).
     * Uses Laravel's asset() so ASSET_URL / APP_URL is respected.
     */
    function app_asset(string $path): string
    {
        return asset(ltrim($path, '/'));
    }
}
