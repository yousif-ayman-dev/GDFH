import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Kanban Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should load kanban board successfully', async ({ page }) => {
    await page.goto('/kanban');
    await expect(page).toHaveURL(/\/kanban$/);
    await expect(page.locator('body')).toContainText(/كانبان|لوحة/i);
  });
});
