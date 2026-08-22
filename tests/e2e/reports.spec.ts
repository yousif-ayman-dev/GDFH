import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Reports Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should load reports and analytics view and render real export actions', async ({ page }) => {
    await page.goto('/reports');
    await expect(page).toHaveURL(/\/reports$/);
    await expect(page.locator('body')).toContainText(/التقارير|التحليلات|Reports/i);

    const pdfBtn = page.locator('a').filter({ hasText: /تصدير PDF/i }).first();
    const csvBtn = page.locator('a').filter({ hasText: /تصدير CSV/i }).first();

    await expect(pdfBtn).toBeVisible();
    await expect(csvBtn).toBeVisible();

    await expect(pdfBtn).toHaveAttribute('href', /\/reports\/export\/pdf/);
    await expect(csvBtn).toHaveAttribute('href', /\/reports\/export\/csv/);
  });
});
