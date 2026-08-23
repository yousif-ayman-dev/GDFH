import { test, expect } from '@playwright/test';
import { login } from './helpers/test-helpers';

test.describe('Realtime Messaging & Notifications Suite', () => {
  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('should load messaging index view and render active chat area', async ({ page }) => {
    await page.goto('/chat');
    await expect(page.locator('h2')).toContainText(/الرسائل والمحادثات المباشرة/i);
    await expect(page.locator('#notification-bell-btn')).toBeVisible();
  });

  test('should open notification bell dropdown on header click', async ({ page }) => {
    await page.goto('/dashboard');
    const bellBtn = page.locator('#notification-bell-btn');
    await expect(bellBtn).toBeVisible();
    await bellBtn.click();

    const dropdown = page.locator('#notification-dropdown');
    await expect(dropdown).toBeVisible();
    await expect(dropdown).toContainText(/الإشعارات/i);
  });
});
