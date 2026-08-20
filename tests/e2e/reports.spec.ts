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

  test('should load reports and analytics view', async ({ page }) => {
    await page.goto('/reports');
    await expect(page).toHaveURL(/\/reports$/);
    await expect(page.locator('body')).toContainText(/التقارير|التحليلات|Reports/i);
  });
});
