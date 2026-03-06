# Storage and asset URL setup

This document explains how file storage and asset URLs work in the app, and what to configure on the server so vehicle images, logos, and other public assets display correctly.

## Architecture

- **Public disk** (`storage/app/public`): Vehicle photos, vehicle documents (insurance, vignette, visite, financing), reservation payment receipts, inspection photos, expense attachments. Accessed via `public/storage` symlink.
- **Customer documents disk** (`storage/app/customer-documents`): Private; CIN and driving licence uploads. Served via controller download route, not direct URL.
- **Static assets**: Logo and icons live in `public/images/` (e.g. `light-logo.png`, `Icon Blue.png`). No symlink needed.

## Central helpers (do not build URLs manually)

- **`storage_public_url($path)`** – Use for any file on the public disk. Pass the path as stored in DB (e.g. `vehicles/1/photo.jpg`). Handles normalization (leading slash, `storage/` prefix). Returns `null` if path is empty.
- **`storage_public_exists($path)`** – Check if a file exists on the public disk (same path normalization).
- **`app_asset($path)`** – Use for static assets in `public/` (e.g. `images/light-logo.png`). Respects `ASSET_URL` / `APP_URL`.
- **`normalize_storage_path($path)`** – When saving a path to DB, normalize it so we store only relative paths (no leading slash, no `storage/` prefix).

Vehicle model exposes **`$vehicle->image_url`** (accessor) which uses `storage_public_url($vehicle->image_path)`. Use that in Blade/JSON instead of building the URL yourself.

## Server checklist

### 1. Create the storage symlink

From the project root:

```bash
php artisan storage:link
```

This creates `public/storage` → `storage/app/public`. Without it, URLs like `/storage/vehicles/1/photo.jpg` will 404.

### 2. Set APP_URL correctly

In `.env`:

- If the app is at the domain root:  
  `APP_URL=https://yourdomain.com`
- If the app is in a subdirectory (e.g. `/app`):  
  `APP_URL=https://yourdomain.com/app`

Wrong `APP_URL` causes wrong asset and storage URLs (e.g. logo and vehicle images 404).

### 3. (Optional) ASSET_URL for CDN or subpath

If you use a CDN or want assets from another base URL:

```env
ASSET_URL=https://cdn.yourdomain.com
# or
ASSET_URL=https://yourdomain.com/app
```

If set, the public disk URL and `app_asset()` use this for the base. Otherwise they use `APP_URL`.

### 4. Web server

- Document root must be the Laravel **`public`** directory (so `public/storage` and `public/images` are reachable).
- If you use a subdirectory (e.g. `/app`), the server must serve the app from that path and `APP_URL` (and optionally `ASSET_URL`) must include `/app`.

### 5. Run path normalization (once)

If you had existing data with paths like `/storage/vehicles/...` or `storage/vehicles/...` in DB, run:

```bash
php artisan migrate
```

The migration `normalize_storage_paths_in_database` cleans those to relative paths (e.g. `vehicles/1/photo.jpg`).

## Debug command

To verify symlink, config, and a sample vehicle image:

```bash
php artisan storage:debug
```

Optional: test a specific vehicle:

```bash
php artisan storage:debug --vehicle=1
```

## Blade / UI rules

- Never use `asset('storage/' . $path)` or raw concatenation. Use `storage_public_url($path)` or `$vehicle->image_url`.
- For the logo and static images, use `app_asset('images/light-logo.png')` (or the relevant path).
- Every `<img>` that uses a stored path must have a fallback when the URL is missing or the image fails to load: use `onerror` to show a placeholder or a placeholder element when `image_url` is null, so users never see a broken image icon.
