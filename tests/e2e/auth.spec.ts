import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test('company_admin can log in and reach dashboard', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 15000 });
    await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible({ timeout: 15000 });
  });

  test('agent can log in and reach dashboard', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await page.fill('input[name="email"]', 'agent@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 15000 });
    await expect(page.getByRole('heading', { name: /dashboard/i })).toBeVisible({ timeout: 15000 });
  });

  test('invalid credentials show error', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'wrong');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/admin\/login/);
    await expect(page.locator('text=invalid').or(page.locator('text=incorrect')).first()).toBeVisible({ timeout: 5000 });
  });
});
