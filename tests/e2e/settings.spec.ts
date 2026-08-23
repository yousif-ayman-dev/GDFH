import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Settings Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should load settings view and toggle tabs successfully', async ({ page }) => {
    await page.goto('/settings');
    await expect(page).toHaveURL(/\/settings$/);
    await expect(page.locator('body')).toContainText(/إعدادات النظام والتفضيلات|Settings/i);

    const profileTab = page.locator('button').filter({ hasText: /الملف الشخصي والصورة/i }).first();
    const securityTab = page.locator('button').filter({ hasText: /الأمان وكلمة المرور/i }).first();
    const notificationsTab = page.locator('button').filter({ hasText: /تفضيلات الإشعارات/i }).first();
    const appearanceTab = page.locator('button').filter({ hasText: /مظهر النظام/i }).first();

    await expect(profileTab).toBeVisible();
    await expect(securityTab).toBeVisible();
    await expect(notificationsTab).toBeVisible();
    await expect(appearanceTab).toBeVisible();

    await securityTab.click();
    await expect(page.locator('body')).toContainText(/تحديث كلمة المرور/i);

    await notificationsTab.click();
    await expect(page.locator('body')).toContainText(/إشعارات البريد الإلكتروني/i);

    await appearanceTab.click();
    await expect(page.locator('body')).toContainText(/الوضع الفاتح|الوضع الداكن/i);
  });
});
