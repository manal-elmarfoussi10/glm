# QA Checklist – GLM App

**Full business scenarios:** See [docs/qa-scenarios.md](qa-scenarios.md) for S1–S12 (onboarding, fleet, customers, reservations, contracts, payments, inspections, alerts, branch scoping, profitability, roles, SuperAdmin).

Run full QA: `composer qa` (Laravel tests then Playwright E2E). For a summary report with failures: `composer qa:report` or `./scripts/qa-report.sh`. Ensure app is running at `BASE_URL` (default `http://127.0.0.1:8000`) and DB is seeded: `php artisan migrate:fresh --seed`.

---

## 1. Auth & roles

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 1.1 | Login as company_admin (admin@company.com / password) | E2E | Playwright: auth.spec.ts |
| 1.2 | Login as agent (agent@company.com / password) | E2E | Playwright: auth.spec.ts |
| 1.3 | Invalid credentials show error | E2E | Playwright: auth.spec.ts |
| 1.4 | Agent cannot access Reports | Feature | RolePermissionsTest::test_agent_cannot_access_reports_route |
| 1.5 | Company admin can access Reports | Feature | RolePermissionsTest::test_company_admin_can_access_reports_route |
| 1.6 | Agent cannot cancel reservation | Feature | RolePermissionsTest::test_agent_cannot_cancel_reservation |

---

## 2. Company data isolation

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 2.1 | Company admin cannot access another company (show, vehicles, reservations, customers) | Feature | CompanyIsolationTest |
| 2.2 | Agent cannot access another company | Feature | CompanyIsolationTest |
| 2.3 | Direct URL to other company’s vehicle returns 404 | Feature | CompanyIsolationTest |

---

## 3. Plan gating & limits

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 3.1 | Starter plan has profitability disabled | Feature | PlanGatingTest::test_starter_plan_has_no_profitability_feature |
| 3.2 | Pro plan has profitability enabled | Feature | PlanGatingTest::test_pro_plan_has_profitability_feature |
| 3.3 | PlanGateService denies feature when plan has it disabled | Feature | PlanGatingTest |
| 3.4 | Vehicle limit reached when at limit | Feature | PlanGatingTest::test_vehicle_limit_reached_returns_true_when_at_limit |
| 3.5 | Starter company sees locked profitability page | Feature | ProfitabilityAccessTest::test_starter_company_sees_locked_profitability_page |
| 3.6 | Pro company can access profitability | Feature | ProfitabilityAccessTest::test_pro_company_can_access_profitability_index |
| 3.7 | S1 Plan gating (Starter locked, Pro full) | E2E | onboarding.spec.ts |

---

## 4. Reservations

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 4.1 | Create reservation (vehicle, customer, dates, total, status) | E2E | reservation.spec.ts |
| 4.2 | Overlap validation: confirmed reservation overlapping existing returns error | Feature | ReservationOverlapTest::test_creating_confirmed_reservation_overlapping_existing_returns_validation_error |
| 4.3 | Confirm draft that would overlap returns error | Feature | ReservationOverlapTest::test_confirming_draft_reservation_that_would_overlap_returns_error |
| 4.4 | Overlap error message visible in UI | E2E | reservation.spec.ts (overlapping reservation) |
| 4.5 | Generate contract from reservation | E2E | reservation.spec.ts |

---

## 5. Vehicles

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 5.1 | Create vehicle (plate, brand, model, category, price, status) | E2E | vehicle.spec.ts |
| 5.2 | Vehicle appears in fleet list | E2E | vehicle.spec.ts |

---

## 6. Customers

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 6.1 | Create customer (name, CIN, phone, email, city) | E2E | customer.spec.ts |

---

## 7. Payments & inspections

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 7.1 | Add manual payment (amount, method, type, date) | E2E | payment-inspection.spec.ts |
| 7.2 | Refund deposit (from reservation detail) | Manual | Reservation show → Remboursement caution |
| 7.3 | Create check-out inspection (fuel, mileage) | E2E | payment-inspection.spec.ts |
| 7.4 | Create check-in inspection + optional photos | Manual | Reservation show → État des lieux retour |

---

## 8. Alerts

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 8.1 | Alerts page loads | E2E | alerts.spec.ts |
| 8.2 | Expiring insurance or “return today” visible (with seeded data) | E2E | alerts.spec.ts |

---

## 9. Demo data (seed)

| # | Scenario | Type | How to verify |
|---|----------|------|----------------|
| 9.1 | Seed creates companies, branches, users, plans | Seeder | php artisan db:seed |
| 9.2 | Seed creates vehicles, customers, reservations, payments, inspections, contract template | Seeder | DemoDataSeeder |
| 9.3 | At least one vehicle with expiring insurance (for alerts) | Seeder | DemoDataSeeder |
| 9.4 | At least one reservation with return today (for alerts) | Seeder | DemoDataSeeder |

---

## One-command run

```bash
# 1. Seed DB (fresh + demo data)
php artisan migrate:fresh --seed

# 2. Start server (in another terminal)
php artisan serve

# 3. Run all tests (Laravel + Playwright)
composer qa
# Or manually: php artisan test && bash scripts/qa-run.sh
```

Optional: `BASE_URL=http://127.0.0.1:8000 npx playwright test` if server is already running.
