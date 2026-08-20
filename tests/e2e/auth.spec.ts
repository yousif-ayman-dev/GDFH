import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Authentication Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should login successfully with valid credentials', async ({ page }) => {
    await login(page);
    await expect(page).toHaveURL(/\/dashboard|\/$/);
    await expect(page.locator('body')).toContainText(/الرئيسية|مرحباً|لوحة التحكم|GDFH/i);
  });

  test('should fail login with invalid password', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('#email')).toBeVisible();

    await page.fill('#email', 'client@gdfh.edu');
    await page.fill('#password', 'wrongpassword123');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.locator('body')).toContainText(/اعتماد|غير صحيحة|invalid|credentials/i);
  });

  test('should logout successfully', async ({ page }) => {
    await login(page);
    
    // Find logout button in sidebar/dropdown
    const logoutBtn = page.locator('form[action*="logout"] button[type="submit"]:visible').first();
    await expect(logoutBtn).toBeVisible();
    await logoutBtn.click();

    await expect(page).toHaveURL(/\/login|\/$/);
  });
});
