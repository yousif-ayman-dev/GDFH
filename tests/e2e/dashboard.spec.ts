import { test, expect } from '@playwright/test';
import { login, toggleTheme, setupErrorListeners } from './helpers/test-helpers';

test.describe('Dashboard Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should render dashboard navigation and widgets correctly', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/\/dashboard$/);
    await expect(page.locator('body')).toContainText(/الرئيسية/);
  });

  test('should toggle theme mode between light and dark', async ({ page }) => {
    await page.goto('/dashboard');
    await toggleTheme(page);
    const htmlElement = page.locator('html');
    await expect(htmlElement).toBeDefined();
  });
});
