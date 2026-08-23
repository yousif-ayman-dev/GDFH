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

  test('should load standalone search portal and filter results by entity type', async ({ page }) => {
    await page.goto('/search');
    await expect(page).toHaveURL(/\/search/);
    await expect(page.locator('body')).toContainText(/بوابة البحث الشاملة|Search Portal/i);

    const searchInput = page.locator('input[name="q"]').first();
    await expect(searchInput).toBeVisible();

    await searchInput.fill('تطوير');
    await searchInput.press('Enter');

    await expect(page).toHaveURL(/\/search\?q=%D8%AA%D8%B7%D9%88%D9%8A%D8%B1/);

    const projectsTab = page.locator('a').filter({ hasText: /المشاريع/i }).first();
    await expect(projectsTab).toBeVisible();
    await projectsTab.click();
    await expect(page).toHaveURL(/type=projects/);
  });
});
