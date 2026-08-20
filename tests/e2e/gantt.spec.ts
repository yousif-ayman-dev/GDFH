import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Gantt Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should load gantt chart page successfully', async ({ page }) => {
    await page.goto('/gantt');
    await expect(page).toHaveURL(/\/gantt$/);
    await expect(page.locator('body')).toContainText(/غانت|مخطط|timeline/i);
  });
});
