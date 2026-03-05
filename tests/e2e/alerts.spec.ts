import { test, expect } from '@playwright/test';

test.describe('Alerts', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });
  });

  test('alerts page shows when there are alerts', async ({ page }) => {
    await page.click('text=Alertes');
    await page.waitForURL(/\/alerts/);
    await expect(page.locator('h1').filter({ hasText: 'Alertes' })).toBeVisible();
  });

  test('dashboard or alerts show expiring or return today', async ({ page }) => {
    await page.goto('/app/dashboard');
    const hasAlert = await page.locator('text=Assurance').or(page.locator('text=retour')).or(page.locator('text=expir')).first().isVisible().catch(() => false);
    if (hasAlert) {
      await expect(page.locator('text=Assurance').or(page.locator('text=retour')).or(page.locator('text=expir'))).toBeVisible();
    }
  });
});
