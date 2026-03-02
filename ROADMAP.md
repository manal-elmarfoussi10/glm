# GLM – Product Feature Roadmap & To-Do

Use this file to track features and the steps to get them working. Check off items as you complete them.

---

## Product development phases (overview)

- **Phase 1:** Foundation – auth, core models, Filament resources  
- **Phase 2:** Core features – main entities, CRUD, relationships  
- **Phase 3:** UX & content – design, copy, media, search  
- **Phase 4:** Polish – performance, security, deployment  

---

## Phase 1 – Foundation

### Authentication & access
- [ ] Filament login working (done ✓)
- [ ] Homepage redirects to `/admin` (done ✓)
- [ ] Define user roles (e.g. Admin, Editor, Viewer) if needed
- [ ] Restrict Filament panel by role (policies / middleware)
- [ ] Optional: “Forgot password” flow
- [ ] Optional: 2FA for admin

### Core data & models
- [ ] List main entities (e.g. Products, Categories, Clients, etc.)
- [ ] Create migrations for each entity
- [ ] Run migrations on production
- [ ] Create Filament Resource(s) for each entity (list, form, filters)

### Base setup
- [ ] Seed initial data (e.g. admin user, default categories) if needed
- [ ] Set correct `APP_URL` and `ASSET_URL` in production
- [ ] Ensure `build/`, `css/`, `js/`, `fonts/` are in document root (done ✓)

---

## Phase 2 – Core features

### For each main entity (repeat pattern)
- [ ] **Entity name:** _________________
  - [ ] Migration + model
  - [ ] Filament Resource (table, form, filters)
  - [ ] Relationships to other models
  - [ ] Validation rules
  - [ ] Optional: soft deletes, ordering, status

### Relationships & logic
- [ ] Define relationships between entities (e.g. Product → Category)
- [ ] Add relation managers in Filament where useful
- [ ] Any business rules (e.g. “draft” vs “published”)

### Data & list UX
- [ ] Bulk actions (e.g. delete, export) if needed
- [ ] Global search in Filament
- [ ] Custom table columns (badges, links, dates)

---

## Phase 3 – UX & content

### Design & branding
- [ ] Filament theme: colors, logo, favicon (match GLM brand)
- [ ] Custom login page branding (optional)
- [ ] Public-facing pages (if any): layout, navigation, footer

### Media & files
- [ ] File uploads (e.g. images for products) – Filament FileUpload
- [ ] Configure `storage` link: `php artisan storage:link`
- [ ] Optional: image resize/optimization

### Search & filters
- [ ] Filters on main Filament tables
- [ ] Optional: full-text or Scout search for large datasets

---

## Phase 4 – Polish

### Performance & caching
- [ ] `php artisan config:cache` and `route:cache` in production
- [ ] Optional: Redis for cache/sessions if needed
- [ ] Optimize N+1 in Filament (eager loading)

### Security
- [ ] `APP_DEBUG=false` in production
- [ ] HTTPS only (APP_URL with https)
- [ ] Rate limiting on login (Filament can do this)
- [ ] Review file upload validation (types, size)

### Deployment & ops
- [ ] Backups: database + `.env`
- [ ] Optional: queue worker for jobs (if you add jobs)
- [ ] Optional: scheduler for recurring tasks (`php artisan schedule:work` or cron)

---

## Step-by-step “get a feature working” (template)

Use this for each new feature:

1. **Define** – What exactly should the feature do? (one sentence)
2. **Data** – New table/columns? New model? Migration.
3. **Model** – Eloquent model, fillable, casts, relationships.
4. **Filament** – Resource or relation manager: list, form, filters.
5. **Rules** – Validation in form request or in Resource.
6. **Test** – Create/edit/delete in `/admin`, then on production after deploy.
7. **Deploy** – `git push`, on server: `git pull`, `php artisan migrate` (if needed), clear caches.

---

## Quick reference – server commands (Hostinger)

```bash
cd ~/domains/glm.marfoussiwebart.com/public_html

# After git pull
php artisan migrate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optional: cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Your feature list (fill in from your roadmap)

Add your own features from the “GLM - Product Feature Roadmap” here, then move them into the phases above and use the step-by-step template.

| # | Feature / epic | Phase | Done |
|---|----------------|-------|------|
| 1 | _Example: Product catalog_ | 2 | ☐ |
| 2 | | | ☐ |
| 3 | | | ☐ |
| 4 | | | ☐ |
| 5 | | | ☐ |

---

*Update this file as you build; check off boxes when each item is done.*
