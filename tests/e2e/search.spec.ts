import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Search Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should render search bar trigger element', async ({ page }) => {
    await page.goto('/dashboard');
    const searchBtn = page.locator('header button').filter({ hasText: /ابحث|Ctrl K/i }).first();
    await expect(searchBtn).toBeVisible();
  });
});
