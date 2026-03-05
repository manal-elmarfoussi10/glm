# GLM – Test accounts (all access)

Use these after: `php artisan migrate:fresh --seed`

**Login URL:** `/admin/login` (or open app and you’ll be redirected)

**Password for all accounts below:** `password`

---

## Platform

| Role          | Email                     | Access |
|---------------|---------------------------|--------|
| **Super Admin** | `superadmin@example.com` | Full platform: companies list, create company, plans, settings, registration requests, subscriptions, journal, admin users, contract templates, upgrade requests. Can open any company (read/manage). |

---

## Companies & roles

### Main Company LLC (Pro plan)
- **Company Admin** – `admin@company.com`  
  Full company access: profile, branches, fleet, vehicles, customers, reservations, contracts, payments, damages, alerts, **reports**, **profitability**, expenses, contract templates, subscription, users. Can cancel reservations.

### Other Company SARL (Pro plan)
- **Agent** – `agent@company.com`  
  Operational only: dashboard, branches (view), fleet, vehicles, customers, reservations, payments, alerts. **No** reports, **no** cancel reservation, **no** contract templates / subscription / users management.

### Starter Company QA (Starter plan)
- **Starter Admin** – `starter-admin@company.com`  
  Same as company admin but **profitability** and (if gated) **reports** show locked / upgrade page. Use to test plan gating (S1).

---

## Quick reference

| Email                     | Password  | Role          | Company           |
|---------------------------|-----------|---------------|-------------------|
| superadmin@example.com    | password  | super_admin   | —                 |
| admin@company.com         | password  | company_admin | Main Company LLC  |
| agent@company.com         | password  | agent         | Other Company SARL|
| starter-admin@company.com | password  | company_admin | Starter Company QA|

---

## URLs (when logged in)

- Dashboard: `/app/dashboard`
- Companies: `/app/companies`
- Alerts: `/app/alerts` (or from dashboard)
- Reservations: `/app/reservations`
- Fleet: `/app/companies/{company}/vehicles`
- Reports: `/app/companies/{company}/reports` (admin only; agent gets 403)
- Rentabilité: `/app/fleet/profitability` (Pro only; Starter sees locked)
- Upgrade / plans: `/app/companies/{company}/upgrade`

Super Admin: `/app/admin/plans`, `/app/admin/users`, `/app/admin/contract-templates`, `/app/admin/settings`, `/app/admin/upgrade-requests`, `/app/registration-requests`, etc.
