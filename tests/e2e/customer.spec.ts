import { test, expect } from '@playwright/test';

test.describe('Customer', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });
    await page.goto('/app/customers');
    await expect(page).toHaveURL(/\/customers/);
  });

  test('create customer with required fields', async ({ page }) => {
    await page.click('text=Nouveau client');
    await page.waitForURL(/\/customers\/create/);

    await page.fill('input[name="name"]', 'E2E Test Client');
    await page.fill('input[name="cin"]', 'QA123456');
    await page.fill('input[name="phone"]', '0611223344');
    await page.fill('input[name="email"]', 'e2e@test.ma');
    await page.fill('input[name="city"]', 'Casablanca');

    await page.click('button[type="submit"]');
    await expect(page.locator('text=E2E Test Client').or(page.locator('text=Client créé'))).toBeVisible({ timeout: 10000 });
  });
});
