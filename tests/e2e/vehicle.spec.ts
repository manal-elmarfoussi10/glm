import { test, expect } from '@playwright/test';

test.describe('Vehicle', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });
    await page.goto('/app/companies');
    await page.click('text=Main Company LLC');
    await expect(page).toHaveURL(/\/companies\/\d+/);
  });

  test('create vehicle with required fields', async ({ page }) => {
    await page.click('text=Flotte');
    await page.waitForURL(/\/vehicles/);
    await page.click('text=Nouveau véhicule');
    await page.waitForURL(/\/vehicles\/create/);

    await page.fill('input[name="plate"]', 'QA-TEST-01');
    await page.fill('input[name="brand"]', 'Renault');
    await page.fill('input[name="model"]', 'Clio');
    await page.selectOption('select[name="partner_category"]', 'economy');
    await page.fill('input[name="daily_price"]', '200');
    await page.selectOption('select[name="status"]', 'available');

    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/vehicles$/);
    await expect(page.locator('text=QA-TEST-01')).toBeVisible();
    await expect(page.locator('text=Véhicule créé')).toBeVisible();
  });
});
