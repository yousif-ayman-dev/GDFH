import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Calendar Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should load calendar view and render new event trigger button', async ({ page }) => {
    await page.goto('/calendar');
    await expect(page).toHaveURL(/\/calendar$/);
    await expect(page.locator('body')).toContainText(/التقويم والمواعيد|Calendar/i);

    const newEventBtn = page.locator('button').filter({ hasText: /حدث جديد/i }).first();
    await expect(newEventBtn).toBeVisible();
  });
});
