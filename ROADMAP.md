# GLM – Product Feature Roadmap & To-Do

Use this file to track features and the steps to get them working. Check off items as you complete them.

---

## Product development phases (overview)

- **Phase 1:** Foundation – auth, core models, Filament resources  
- **Phase 2:** Core features – main entities, CRUD, relationships  
- **Phase 3:** UX & content – design, copy, media, search  
- **Phase 4:** Polish – performance, security, deployment  

---

## Roles & Access Control (Final Structure)

### 🌍 Platform Level (GLM)

| Role | Key capabilities |
|------|------------------|
| **super_admin** | Manage companies, subscriptions, system analytics, suspend accounts. Full access. |
| **support** | View companies, assist users, reset passwords, view logs. No financial deep access. |

### 🏢 Company Level (Rental Agency)

| Role | Key capabilities |
|------|------------------|
| **company_admin** | Add managers & accountant, create branches, manage vehicles/clients, reservations, contracts, financial dashboards, pricing rules, export reports. Full control inside company. |
| **manager** | Manage vehicles (branch), create reservations, add clients, generate contracts, inspections, mark payments. Cannot: manage subscription, add/remove admins, change global settings. |
| **accountant** | View reservations, mark payments, track cheques, export reports, revenue dashboards. Cannot: create reservations, modify vehicles, delete data. |

### Architecture

- **One company → multiple branches**
- **Users:** `id`, `company_id`, `branch_id` (nullable for admin), `role`
- **Roles:** `super_admin`, `support`, `company_admin`, `manager`, `accountant`
- **MVP:** 3 roles inside company: Admin, Manager, Accountant (no Agent)

---

## Phase 1 – Foundation

### Authentication & access
- [ ] Filament login working (done ✓)
- [ ] Homepage redirects to `/admin` (done ✓)
- [ ] Define user roles (see Roles & Access Control above)
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

## Deploy (shared hosting – no Node/npm on server)

Because the server cannot run `npm run build`, you must **build assets locally** and commit them.

### Before each deploy (when CSS/JS change)

```bash
npm run build
git add public/build/
git commit -m "Build assets for production"
git push
```

### Server `.env` (required)

```env
APP_URL=https://glm.marfoussiwebart.com
ASSET_URL=https://glm.marfoussiwebart.com
```

### Checklist

- [ ] Run `npm run build` locally before pushing CSS/JS changes
- [ ] Commit `public/build/` with your code
- [ ] `APP_URL` and `ASSET_URL` set correctly on server
- [ ] After deploy: `php artisan optimize:clear` if styles don’t update

---

## Proposed implementation order

Start with:

1. **Phase 1 – Roles & migrations**
   - [ ] Add `role` column to users (enum: `super_admin`, `support`, `company_admin`, `manager`, `accountant`)
   - [ ] Add `company_id` and `branch_id` (nullable) to users table
   - [ ] Create `companies` and `branches` tables
   - [ ] Create roles enum or config
   - [ ] Seed super_admin user

2. **Phase 2 – Authorization**
   - [ ] Implement Filament policies / middleware to restrict panel by role
   - [ ] Super Admin: sees everything; Support: limited company view
   - [ ] Company users: scope to `company_id` (and `branch_id` for managers)

3. **Phase 3 – Company & branch CRUD**
   - [ ] Filament resources: Company, Branch
   - [ ] Company Admin can create branches and invite users

4. **Phase 4 – Role-specific UI**
   - [ ] Hide/show Filament resources and actions by role
   - [ ] Manager: branch-scoped vehicles, reservations, clients
   - [ ] Accountant: read-only + payments, reports

---

## Your feature list (fill in from your roadmap)

Add your own features from the “GLM - Product Feature Roadmap” here, then move them into the phases above and use the step-by-step template.

| # | Feature / epic | Phase | Done |
|---|----------------|-------|------|
| 1 | Roles & migrations (companies, branches, users) | 1 | ☐ |
| 2 | Authorization by role (policies, middleware) | 1 | ☐ |
| 3 | Company & Branch CRUD | 2 | ☐ |
| 4 | Role-specific Filament UI | 2 | ☐ |
| 5 | _Add more as you build_ | | ☐ |

---

*Update this file as you build; check off boxes when each item is done.*
