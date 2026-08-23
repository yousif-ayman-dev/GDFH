import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Marketplace Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should load marketplace view and render service create action buttons', async ({ page }) => {
    await page.goto('/marketplace');
    await expect(page).toHaveURL(/\/marketplace/);
    await expect(page.locator('body')).toContainText(/سوق المستقلين والخدمات البرمجية|Marketplace/i);

    const createBtn = page.locator('a').filter({ hasText: /إضافة خدمة جديدة/i }).first();
    const profileEditBtn = page.locator('a').filter({ hasText: /إعدادات المستقل/i }).first();

    await expect(createBtn).toBeVisible();
    await expect(profileEditBtn).toBeVisible();

    await createBtn.click();
    await expect(page).toHaveURL(/\/marketplace\/services\/create$/);
    await expect(page.locator('body')).toContainText(/إضافة خدمة جديدة إلى السوق/i);
  });
});
