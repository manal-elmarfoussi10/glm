<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Deployment (Hostinger)

1. **Clone on the server** (SSH or File Manager):  
   `git clone https://github.com/manal-elmarfoussi10/glm.git` into your domain’s `public_html` or the folder Hostinger uses for the app.

2. **Point the document root** to the app’s `public` folder (e.g. `public_html/glm/public` or set the domain root to that path in the Hostinger panel).

3. **On the server**, from the project root (parent of `public`):
   - `composer install --no-dev --optimize-autoloader`
   - `cp .env.example .env` then edit `.env` (database, `APP_KEY`, `APP_URL`)
   - `php artisan key:generate`
   - `php artisan migrate --force`
   - `npm ci && npm run build`
   - Ensure `storage` and `bootstrap/cache` are writable:  
     `chmod -R 775 storage bootstrap/cache`

4. **Optional**: Create a Filament admin user:  
   `php artisan make:filament-user`

### Shared hosting (cannot change document root)

If you **cannot** set the document root to `public` (e.g. Hostinger with fixed `public_html`):

1. **Put the app outside `public_html`**  
   Move the whole project so it sits **next to** `public_html`, not inside it, e.g.:
   - `/home/username/glm/` ← full Laravel app (app, bootstrap, config, .env, vendor, **and** the `public` folder)
   - `/home/username/public_html/` ← document root (unchanged)

2. **Copy the contents of `glm/public/` into `public_html/`**  
   So `public_html/` contains: `index.php`, `.htaccess`, `build/`, `css/`, `js/`, `fonts/`, `favicon.ico`, `robots.txt`, etc. (everything that is inside `glm/public/`).

3. **Use the shared-hosting entry point**  
   Replace `public_html/index.php` with the contents of `glm/public/index.shared.php` (or copy `index.shared.php` to `public_html/index.php`).  
   That file tells PHP to load Laravel from `../glm`. If your app folder is not named `glm`, edit the `$appBasePath` line in `index.shared.php` to match (e.g. `'/../myapp'`).

4. **Leave `.env` and the rest in** `/home/username/glm/`. The site will run from `public_html` but use the app in `glm/`.

Repo: [https://github.com/manal-elmarfoussi10/glm](https://github.com/manal-elmarfoussi10/glm)

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
