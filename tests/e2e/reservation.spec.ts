import { test, expect } from '@playwright/test';

test.describe('Reservation', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });
  });

  test('create reservation and generate contract', async ({ page }) => {
    await page.goto('/app/reservations');
    await page.waitForURL(/\/reservations/);
    await page.click('text=Nouvelle réservation');
    await page.waitForURL(/\/reservations\/create/);

    await page.selectOption('select[name="vehicle_id"]', { index: 1 });
    await page.selectOption('select[name="customer_id"]', { index: 1 });
    const start = new Date();
    start.setDate(start.getDate() + 14);
    const end = new Date(start);
    end.setDate(end.getDate() + 3);
    await page.fill('input[name="start_at"]', start.toISOString().slice(0, 16));
    await page.fill('input[name="end_at"]', end.toISOString().slice(0, 16));
    await page.fill('input[name="total_price"]', '750');
    await page.selectOption('select[name="status"]', 'confirmed');

    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/reservations\/\d+$/);
    await expect(page.locator('text=Réservation confirmée').or(page.locator('text=Brouillon enregistré'))).toBeVisible({ timeout: 10000 });

    await page.click('text=Générer le contrat');
    await page.click('button[type="submit"]');
    await expect(page.locator('text=Contrat généré')).toBeVisible({ timeout: 5000 });
  });

  test('overlapping reservation shows validation error', async ({ page }) => {
    await page.goto('/app/reservations');
    await page.click('text=Nouvelle réservation');
    await page.waitForURL(/\/reservations\/create/);

    await page.selectOption('select[name="vehicle_id"]', { index: 1 });
    await page.selectOption('select[name="customer_id"]', { index: 1 });
    const start = new Date();
    start.setDate(start.getDate() + 1);
    const end = new Date(start);
    end.setDate(end.getDate() + 5);
    await page.fill('input[name="start_at"]', start.toISOString().slice(0, 16));
    await page.fill('input[name="end_at"]', end.toISOString().slice(0, 16));
    await page.fill('input[name="total_price"]', '500');
    await page.selectOption('select[name="status"]', 'confirmed');
    await page.click('button[type="submit"]');

    const err = page.locator('text=déjà réservé').or(page.locator('[role="alert"]'));
    await expect(err.first()).toBeVisible({ timeout: 8000 });
  });
});
