# GLM – QA Scenarios (End-to-End Business Flows)

This document describes complete business scenarios used to verify the system is fully connected. **Only existing features are tested.** Gaps are listed where behaviour is not implemented.

---

## How to run QA

```bash
# 1. Seed database (required for E2E)
php artisan migrate:fresh --seed

# 2. Start server (separate terminal)
php artisan serve

# 3. Run all tests
composer qa
# Laravel tests first, then Playwright E2E.

# 4. Run with report (passed/failed + failure details)
composer qa:report
# Or: ./scripts/qa-report.sh
```

- **Laravel Feature tests:** `./vendor/bin/phpunit` (or `composer test`)
- **Playwright E2E:** `npx playwright test` (server must be running at BASE_URL, default `http://127.0.0.1:8000`)
- **QA report:** `composer qa:report` runs both and prints a summary; on failure, shows failed scenarios and log paths.

---

## S1 – Onboarding Company (manual subscription)

**Goal:** SuperAdmin creates a company, assigns a plan, activates it; company admin logs in, completes profile, creates a branch; plan gating works (allowed features visible, locked ones show upgrade page).

### Preconditions
- Plans exist (Starter, Pro) – seeded by `DemoDataSeeder`.
- SuperAdmin user: `superadmin@example.com` / `password`.

### Step-by-step (UI)
1. **SuperAdmin:** Log in at `/admin/login` → go to App → Companies → Create company. Enter name, contact, select plan (e.g. Starter), save.
2. **SuperAdmin:** Open company → assign plan if not set; activate company (status active). Create or link a user as company_admin (e.g. via Admin → Users or company users).
3. **Company admin:** Log in with that user → Dashboard. Open company profile (Companies → [company] → Edit). Complete profile (phone, email, city, address). Save.
4. **Company admin:** Branches → Create branch. Enter name, city, address, save.
5. **Company admin:** Navigate to Reports (if plan = Starter, reports may be gated). Navigate to Flotte → Rentabilité (if Starter, expect locked / upgrade page).

### Expected results
- Company is created and visible in companies list.
- Company admin can edit company and create branch.
- **Plan gating:** Starter: Reports and Rentabilité flotte show upgrade/locked view. Pro: Reports and Rentabilité accessible.
- **Gap:** Manual “assign plan + activate” may be via Filament Admin or App Admin users; exact UI depends on implementation.

### Edge cases
- Company with no plan: plan gating treats as “no limit” (all features allowed) until plan is set.
- Branch limit: Starter has `limit_branches = 1`; creating a second branch may be blocked by plan gate (if enforced on create).

---

## S2 – Fleet + Compliance

**Goal:** Add vehicle with insurance / vignette / visite dates and optional financing; alerts appear when dates are near or expired; vehicle status is consistent everywhere.

### Preconditions
- Company with at least one branch. User company_admin or agent. Seeded: Main Company LLC, branch Headquarters, admin@company.com.

### Step-by-step (UI)
1. Flotte → Create vehicle. Fill: branch, plate, brand, model, category, daily price, status. Add insurance end date (e.g. 7 days from today), optional vignette year, visite expiry, financing (monthly payment, bank). Save.
2. Open vehicle detail: compliance section shows insurance/vignette/visite and status (OK / expiring / expired).
3. Go to Alertes: list shows “Assurance – [plate]” (and optionally Visite / Vignette) with correct severity (info / warning / urgent by days left).
4. Edit vehicle: change status to Maintenance. Verify vehicle appears with status “maintenance” in fleet list and is not offered for new reservations if availability logic excludes it.

### Expected results
- Vehicle is stored with all compliance and financing fields.
- Alertes center shows vehicle_compliance alerts for insurance (and visite/vignette if set and within 30 days).
- Vehicle status (available / maintenance / inactive) is reflected in fleet list and vehicle detail.
- **Gap:** “Vehicle status updates everywhere” (e.g. excluding from reservation vehicle picker when maintenance) – verify in reservation create: only available vehicles may be listed if filtering is implemented.

### Edge cases
- Insurance expired (past date): alert severity urgent, “Expirée depuis X j”.
- Multiple vehicles with different expiry dates: all appear in alerts with correct identifiers; mark done / snooze applies per alert.

---

## S3 – Customer onboarding

**Goal:** Add customer with CIN, permit, optional docs; docs stored and visible; search works by CIN / phone / name.

### Preconditions
- Company with branch. Logged in as company_admin or agent.

### Step-by-step (UI)
1. Clients → Create customer. Enter name, CIN, phone, email, city. Optionally upload CIN document, permit document. Save.
2. Customer detail: documents (if any) are listed and downloadable.
3. Clients list: use search/filter by CIN, phone, or name. Verify customer appears.

### Expected results
- Customer is created; CIN and contact data stored.
- If document upload exists: files stored (e.g. in storage), visible on customer show page.
- Search/filter on customers index returns correct rows by CIN, phone, name.
- **Gap:** Document upload fields (CIN/permis) – confirm in CompanyCustomerController and customer create/edit views; if not implemented, list as gap.

### Edge cases
- Duplicate CIN: validation may prevent or warn (depends on unique rule on customers.cin per company).
- Empty search: list shows all company customers (paginated).

---

## S4 – Reservation full lifecycle

**Goal:** Create reservation (vehicle, client, dates); availability is respected; overlap is prevented; price is calculated; confirm → start rental → complete rental.

### Preconditions
- Company with branches, vehicles (available), customers. Contract template optional for contract step.

### Step-by-step (UI)
1. Réservations → Create. Select vehicle, customer, start date/time, end date/time, pickup/return branches. Verify total price is calculated (e.g. from daily price × days). Save as draft.
2. Try to create another confirmed reservation for the same vehicle with overlapping dates: expect validation error “déjà réservé” (or equivalent).
3. Open draft reservation → Confirm. Status becomes Confirmed.
4. On start date: Open reservation → “Démarrer la location” (Start rental). Status becomes In progress.
5. On end date: “Clôturer la location” (Complete rental). Status becomes Completed.

### Expected results
- Reservation is created with correct vehicle, customer, branches, dates, total_price.
- Overlap validation: confirmed reservation cannot overlap existing confirmed/in_progress for same vehicle; error on create or confirm.
- Price is calculated (e.g. daily_price × days; formula as in app).
- Status flow: draft → confirmed → in_progress → completed. Cancel available for draft/confirmed.
- **Gap:** Availability “blocked” in UI (e.g. greyed-out dates) depends on vehicle-availability endpoint/UI; overlap is enforced in backend.

### Edge cases
- Same vehicle, same day, different times: overlap logic is date-based or datetime-based (check ReservationController overlap rule).
- Return branch different from pickup: allowed if both belong to company; reservation stores both branch IDs.

---

## S5 – Contract generation

**Goal:** Generate contract from template for a reservation; placeholders replaced; snapshot saved; later template edits do not change already-generated contract.

### Preconditions
- Company with default contract template (placeholders e.g. `{{client_name}}`, `{{vehicle_plate}}`, `{{rental_start_date}}`, `{{rental_end_date}}`, `{{total_amount}}`, `{{deposit_amount}}`). Reservation in confirmed or in_progress.

### Step-by-step (UI)
1. Open reservation → Contract tab/section. Click “Générer le contrat” (or equivalent).
2. Preview: placeholders are replaced with reservation data (client name, vehicle plate, dates, total, deposit).
3. Confirm generation. Contract is saved (snapshot_html or equivalent).
4. Edit company contract template (change wording). Reopen same reservation contract: content is unchanged (snapshot), not the new template.

### Expected results
- Contract generation creates a snapshot (e.g. ReservationContract with snapshot_html) with placeholders replaced.
- Placeholders match config/contract_placeholders (e.g. client_name, vehicle_plate, rental_start_date, rental_end_date, total_amount, deposit_amount).
- Template edit does not alter existing generated contracts; only new generations use the new template.
- **Gap:** Print/PDF export if implemented; otherwise manual check.

### Edge cases
- No default template: UI should prevent generation or show error.
- Reservation without deposit: deposit_amount placeholder shows 0 or “–”.

---

## S6 – Manual payments

**Goal:** Add deposit and rental payment; optionally refund deposit; totals update; remaining amount correct; status badges correct; receipt available.

### Preconditions
- Reservation (confirmed or in_progress) with total_price and optional deposit. Company with branch.

### Step-by-step (UI)
1. Open reservation → Paiements. Add payment: type = Caution (deposit), amount = deposit, method (cash/card/transfer), date. Save.
2. Add payment: type = Location (rental), amount = remaining or partial, method, date. Save.
3. Verify: total paid, remaining amount, payment status (e.g. “paid” when remaining = 0).
4. Refund deposit: use “Rembourser la caution” (if available). Verify refund is recorded (e.g. negative or refund type) and remaining/caution status updated.
5. Open “Reçu” (receipt): PDF or printable page with reservation and payments.

### Expected results
- Payments are stored with branch_id, type, amount, method, paid_at.
- Remaining amount = total_price − sum(payments where type = rental) (deposit not counted in “remaining” for payment status, or per business rule).
- Payment status badge: e.g. “paid” when remaining ≤ 0; “unpaid” or “partial” otherwise.
- Refund deposit: creates a refund payment or marks deposit as returned; totals consistent.
- Receipt route returns 200 and shows reservation + payments.
- **Gap:** Exact formula for “remaining” (including deposit handling) as in Reservation model/controller.

### Edge cases
- Overpayment: remaining can go negative or validation prevents; behaviour should be consistent.
- Multiple partial rental payments: remaining decreases with each until 0.

---

## S7 – Inspection (état des lieux)

**Goal:** Check-out inspection (mileage, fuel, photos); then check-in inspection with optional new damages and fees; fees reflected; photos saved; actions logged.

### Preconditions
- Reservation in_progress (or confirmed and start rental done). Company can store inspection and inspection_photos.

### Step-by-step (UI)
1. Open reservation → État des lieux (or Inspections). Create check-out (sortie) inspection: mileage, fuel level, notes, upload photos. Save.
2. When closing rental: Create check-in (retour) inspection: mileage, fuel, notes; add damage (description, fee if applicable); upload photos. Save.
3. Verify: inspection records appear on reservation; photos listed; damage fee (if any) reflected in reservation or damages list.
4. Activity or audit: inspection creation is logged (if activity log exists for inspections).

### Expected results
- Inspection stored with type (out/in), reservation_id, mileage, fuel_level, notes, inspected_at.
- Photos stored (ReservationInspectionPhoto); path and optional caption.
- Damage/fee: if app has damages linked to reservation or inspection, fee is stored and visible (e.g. Damages list, or on reservation).
- **Gap:** “Fees reflected” – confirm whether inspection has a fee field or damages are separate; both may appear in payments or damages index.

### Edge cases
- Check-in without check-out: app may allow or require order (out then in).
- Delete inspection photo: route/button removes photo and record.

---

## S8 – Alerts workflow

**Goal:** Insurance expiry alert, “return today” alert, unpaid remaining alert; alerts show in Alertes center; mark done / snooze works; links open correct record.

### Preconditions
- Vehicle with insurance_end_date in 7 days (or past). Reservation with end_at = today (return today). Reservation with remaining_amount > 0. Seeded by DemoDataSeeder (e.g. AB-12345 insurance in 7 days; RES-*-001 return today; RES-*-002 unpaid).

### Step-by-step (UI)
1. Open Alertes (company alerts). List shows at least: one “Assurance – [plate]”, one “Retour aujourd’hui – [reference]”, one “Solde à régler – [reference]” (if such reservation exists).
2. Click “Marquer comme traité” on one alert: alert disappears (or moves to done).
3. Click “Reporter” (snooze) on another: choose days; alert disappears until snooze_until.
4. Click link on an alert: opens correct vehicle or reservation page.

### Expected results
- Alert types: vehicle_compliance (insurance, visite, vignette), reservation_return (return today), payment_due (unpaid remaining).
- Severity and text match AlertService (e.g. “Expire dans 7 j”, “Retour aujourd’hui”, “X MAD restants”).
- Mark done: CompanyAlertDismissal with action = done; alert no longer in list.
- Snooze: dismissal with snooze_until; alert hidden until that date.
- related_url opens the correct company context and resource (vehicle show, reservation show with optional tab).
- **Gap:** “Return tomorrow” (reservation_start) alert if implemented; same workflow.

### Edge cases
- Filter by type/severity: alerts index supports query params; list filters correctly.
- Same vehicle, multiple compliance types (insurance + visite): two alerts; dismiss one does not remove the other.

---

## S9 – Branch scoping

**Goal:** Multiple branches; vehicles and users assigned to branches; branch filters work in fleet, reservations, payments, reports.

### Preconditions
- Company with at least 2 branches. Vehicles in different branches. Reservations/payments with different pickup_branch_id / branch_id. Seeded: Main Company LLC has Headquarters + Agence Casa Nord; vehicles and reservations per branch.

### Step-by-step (UI)
1. Flotte → Filter by branch (dropdown or query). Select branch A: only vehicles of branch A listed. Select branch B: only branch B. Clear: all vehicles.
2. Réservations → Filter by branch (if available): list shows only reservations for selected branch (pickup/return/vehicle’s branch).
3. Paiements → Filter by branch (if available): list shows only payments for selected branch.
4. Rapports → Date range and optional branch (if implemented): data scoped to branch.

### Expected results
- Vehicles index: branch_id filter applied; URL or form sends branch_id; results filtered.
- Reservations index: branch filter (pickup_branch_id or return_branch_id or vehicle.branch_id) applied when implemented.
- Payments index: branch_id filter applied when implemented.
- Reports: if branch filter exists, revenue/counts respect branch.
- **Gap:** Reports and Payments branch filter may not exist; document behaviour (all branches vs filter).

### Edge cases
- User with branch_id: may see only their branch by default (if UI restricts); or see all with filter.
- New reservation: vehicle list can be scoped by branch in create form if implemented.

---

## S10 – Profitability

**Goal:** Add expenses linked to vehicle (and optional financing cost); profitability page shows revenue − cost formula; date filters apply.

### Preconditions
- Company on Pro plan (profitability feature enabled). Vehicles with reservations (revenue) and optional financing; expenses linked to vehicles. Seeded: Main Company LLC is Pro; vehicles with insurance/financing; add expense via Expenses if needed.

### Step-by-step (UI)
1. Add expense (company_admin): Dépenses → Create. Select vehicle, category, amount, date. Save.
2. Flotte → Rentabilité. Select date range (from, to). Page shows fleet overview: revenue total, cost total (financing, insurance, vignette, maintenance, expenses), net profit; per-vehicle rows.
3. Open one vehicle profitability: detail shows revenue breakdown (reservations), cost breakdown (expenses, financing prorated, etc.), net.
4. Change date range: numbers update (only reservations and expenses in range).

### Expected results
- Profitability page returns 200 for Pro company; Starter sees locked/upgrade view.
- Formula: profit = revenue − (financing + insurance + vignette + maintenance + expenses); revenue from completed/in_progress reservations in range; costs prorated by days/months/years in range (as in ProfitabilityService).
- Expense linked to vehicle appears in that vehicle’s cost and in fleet total.
- Date filters (from, to) correctly restrict reservations and prorate costs.
- **Gap:** Exact proration rules (e.g. insurance annual / 365 × days) as in ProfitabilityService.

### Edge cases
- No expenses: cost = financing + insurance + vignette (prorated).
- Vehicle with no reservations in range: revenue 0, cost still shown (prorated).

---

## S11 – Role permissions

**Goal:** Agent vs company_admin: agent cannot access forbidden pages/actions (templates, settings, reports if restricted); company_admin can.

### Preconditions
- Two users: company_admin (admin@company.com), agent (agent@company.com) for same company. Seeded.

### Step-by-step (UI)
1. **Agent:** Log in. Open Reports (e.g. /app/reports or company reports): expect 403 Forbidden.
2. **Agent:** Open Contract templates (company): if route is company_admin_only, expect 403; otherwise allowed per implementation.
3. **Agent:** Try to cancel a reservation (e.g. Cancel button): expect 403 or button hidden.
4. **Agent:** Open Flotte, Clients, Réservations, Alertes, Paiements: expect 200 (operational access).
5. **Company admin:** Same flows: Reports 200, cancel reservation allowed, contract templates and subscription management allowed.

### Expected results
- Reports: company_admin_only middleware → agent gets 403.
- Cancel reservation: controller or policy restricts to company_admin → agent 403.
- Contract templates, subscription (change plan, activate, suspend): company_admin_only → agent 403.
- Fleet, customers, reservations, alerts, payments (index): company_operational → both roles 200 for their company.
- **Gap:** Expenses create/edit/delete are company_admin_only; agent read-only on index if implemented.

### Edge cases
- Agent with branch_id: may have branch-scoped data in some views (implementation-dependent).
- Direct URL: agent opening /app/companies/{id}/reports gets 403 when id is their company.

---

## S12 – SuperAdmin 360 read-only

**Goal:** SuperAdmin can view all company data and export; read-only enforced where applicable; audit logs recorded.

### Preconditions
- SuperAdmin user. Companies with data. Audit log or activity log enabled if applicable.

### Step-by-step (UI)
1. **SuperAdmin:** Log in. Dashboard shows platform-level links (registration requests, subscriptions, journal, etc.).
2. Companies list: all companies visible. Open a company (show): company detail, branches, link to fleet/reservations/customers etc. No edit/delete for company if read-only; or full access depending on design.
3. Open company’s reservations (or fleet): view list and detail. Export reports (company reports export CSV) if accessible.
4. Journal (Audit log): list shows actions (e.g. vehicle created, reservation confirmed) with user and company if implemented.
5. Registration requests: view, approve, reject. Subscriptions: view, extend trial, update status.

### Expected results
- SuperAdmin can open any company and navigate to vehicles, reservations, customers, alerts, payments, reports (no company_operational denial for super_admin; check middleware).
- **Gap:** “Read-only” may not be enforced: SuperAdmin might have full CRUD on companies; document actual behaviour. If “support” role exists, it may have read-only vs super_admin full.
- Export: company reports export CSV returns file for that company.
- Audit log: entries created for key actions (vehicle, reservation, payment, etc.) with company_id, user_id; visible in Journal.
- **Gap:** Ensure super_admin is not blocked by company_operational (e.g. support can access companies; super_admin may use same or different routes).

### Edge cases
- SuperAdmin editing a company’s subscription/plan: allowed via platform admin routes.
- Audit log filter by company or user: if implemented, filter works.

---

## Scenario ↔ Test mapping

| Scenario | Laravel Feature | Playwright E2E | Notes |
|----------|-----------------|----------------|-------|
| S1 Onboarding | PlanGatingTest, Company create (if exists) | onboarding.spec.ts | Plan gate + branch create |
| S2 Fleet + Compliance | – | vehicle.spec.ts, alerts.spec.ts | Alerts from vehicle compliance |
| S3 Customer | – | customer.spec.ts | Search if implemented |
| S4 Reservation lifecycle | ReservationOverlapTest | reservation.spec.ts | Overlap + confirm/start/complete |
| S5 Contract | – | reservation.spec.ts (contract generate) | Snapshot in backend |
| S6 Payments | – | payment-inspection.spec.ts | Refund deposit manual or E2E |
| S7 Inspection | – | payment-inspection.spec.ts | Check-out/in + photos |
| S8 Alerts | – | alerts.spec.ts | Mark done/snooze, links |
| S9 Branch scoping | BranchScopingTest | Optional E2E filter | Vehicles/reservations branch_id |
| S10 Profitability | PlanGatingTest, ProfitabilityAccessTest | onboarding.spec.ts (Pro) | Pro only, locked for Starter |
| S11 Role permissions | RolePermissionsTest | auth.spec.ts (agent vs admin) | 403 for agent on reports/cancel |
| S12 SuperAdmin | – | Optional admin.spec.ts | View company + export |

---

## Gaps summary (features not implemented or not verified)

- **Company create by SuperAdmin:** Via Filament or App admin; exact route/UI may differ.
- **Document upload (customer CIN/permis):** Confirm presence in customer create/edit.
- **Reservation availability UI:** Date picker blocking overlapping dates (backend overlap is tested).
- **Reports/Payments branch filter:** Document if present.
- **SuperAdmin read-only vs full CRUD:** Document actual middleware/routes.
- **Audit log coverage:** Which actions are logged (vehicle, reservation, payment, etc.).
