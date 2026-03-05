# Hostinger deployment – fix “no design” (CSS/JS not loading)

Domain: **https://glm.marfoussiwebart.com**

## 1. Root `.htaccess` (so `/` and `/build/*` go to `public/`)

- In your **project root** there is a file: **`public_html_htaccess.txt`**
- **On the server:** in the **`public_html`** folder (the one that contains the `public` folder), create or replace **`.htaccess`** with the contents of `public_html_htaccess.txt`.
- So: **`public_html/.htaccess`** must contain the rewrite rules that send all requests into **`public/`** (where `index.php` and `build/` live).

Without this, the server looks for `/build/assets/...` in `public_html/build/` instead of `public_html/public/build/`, so CSS/JS return 404 and the design does not load.

## 2. `.env` on the server

In **`public_html/.env`** (or wherever your `.env` is on Hostinger), set:

```env
APP_URL=https://glm.marfoussiwebart.com
ASSET_URL=https://glm.marfoussiwebart.com
```

(No trailing slash. Use `http://` only if the site is not on HTTPS.)

Then run (SSH or Hostinger “Run PHP script” / terminal):

```bash
php artisan config:clear
php artisan config:cache
```

## 3. Check that built assets exist

On the server, confirm:

- **`public_html/public/build/manifest.json`** exists
- **`public_html/public/build/assets/`** contains files like `auth-41mCgi4e.css`, `app-BBTbW0MI.css`, etc.

If not, upload the **`public/build/`** folder from your local project (after `npm run build`) into **`public_html/public/build/`**.

## 4. Quick check in the browser

1. Open: `https://glm.marfoussiwebart.com/admin/login`
2. Open DevTools (F12) → **Network** tab → reload.
3. Find requests to **`/build/assets/...`** (or `.../public/build/assets/...`):
   - **Status 200** → assets are loading; if the page is still unstyled, clear browser cache or check for other CSS/JS errors.
   - **Status 404** → the rewrite or path is wrong; double‑check step 1 and that `public/build/` is in the right place.

## Summary

| What | Where |
|------|--------|
| Rewrite rules | `public_html/.htaccess` (contents from `public_html_htaccess.txt`) |
| APP_URL / ASSET_URL | `public_html/.env` |
| Built CSS/JS | `public_html/public/build/` |
