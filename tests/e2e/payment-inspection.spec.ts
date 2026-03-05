import { test, expect } from '@playwright/test';

test.describe('Payment and inspection', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });
  });

  test('open first reservation and add payment', async ({ page }) => {
    await page.goto('/app/reservations');
    await page.waitForURL(/\/reservations/);
    await page.click('table tbody tr a').first();
    await page.waitForURL(/\/reservations\/\d+$/);

    await page.click('text=Paiements').or(page.click('text=Enregistrer un paiement'));
    await page.fill('input[name="amount"]', '100');
    await page.selectOption('select[name="method"]', 'cash');
    await page.selectOption('select[name="type"]', 'rental');
    await page.fill('input[name="paid_at"]', new Date().toISOString().slice(0, 10));
    await page.click('form button[type="submit"]');
    await expect(page.locator('text=Paiement enregistré')).toBeVisible({ timeout: 5000 });
  });

  test('add check-out inspection', async ({ page }) => {
    await page.goto('/app/reservations');
    await page.click('table tbody tr a').first();
    await page.waitForURL(/\/reservations\/\d+$/);

    await page.click('text=État des lieux').or(page.click('text=Départ'));
    await page.selectOption('select[name="fuel_level"]', 'plein');
    await page.fill('input[name="mileage"]', '15000');
    await page.click('form button[type="submit"]');
    await expect(page.locator('text=État des lieux').or(page.locator('text=enregistré'))).toBeVisible({ timeout: 8000 });
  });
});
