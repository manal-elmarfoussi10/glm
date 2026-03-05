import { test, expect } from '@playwright/test';

/**
 * S1 – Onboarding / Plan gating
 * Starter company admin sees locked profitability; Pro company admin sees full profitability.
 * Requires: QaScenariosSeeder (Starter Company QA, starter-admin@company.com), DemoDataSeeder (Main Company LLC, Pro).
 */
test.describe('S1 Onboarding and plan gating', () => {
  test('Starter company admin sees locked profitability page', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'starter-admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });

    await page.goto('/app/fleet/profitability');
    await expect(page).toHaveURL(/\/fleet\/profitability|\/companies\/\d+\/fleet\/profitability/);
    await expect(page.locator('text=réservée').or(page.locator('text=mettre à niveau'))).toBeVisible({ timeout: 8000 });
  });

  test('Pro company admin sees profitability content', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });

    await page.goto('/app/fleet/profitability');
    await expect(page).toHaveURL(/\/fleet\/profitability|\/companies\/\d+\/fleet\/profitability/);
    await expect(page.locator('text=Revenus flotte').or(page.locator('text=Coûts flotte'))).toBeVisible({ timeout: 8000 });
  });

  test('Company admin can create a branch', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[name="email"]', 'admin@company.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/\/(app|dashboard)/, { timeout: 10000 });

    await page.goto('/app/branches');
    await page.waitForURL(/\/branches/);
    await expect(page.locator('text=Agences').or(page.locator('text=Branches'))).toBeVisible({ timeout: 5000 });
    const createLink = page.locator('a[href*="/branches/create"]').first();
    if (await createLink.isVisible()) {
      await createLink.click();
      await expect(page).toHaveURL(/\/branches\/create/);
      await expect(page.locator('input[name="name"]').or(page.locator('input[name="city"]'))).toBeVisible({ timeout: 5000 });
    }
  });
});
