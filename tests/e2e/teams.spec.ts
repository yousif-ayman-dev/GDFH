import { test, expect } from '@playwright/test';
import { login, createTeam, setupErrorListeners } from './helpers/test-helpers';

test.describe('Teams Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should display teams index page', async ({ page }) => {
    await page.goto('/teams');
    await expect(page).toHaveURL(/\/teams$/);
    await expect(page.locator('body')).toContainText(/الفرق/);
  });

  test('should create a new team with dynamic locators', async ({ page }) => {
    const teamName = `Enterprise E2E Team ${Date.now()}`;
    await createTeam(page, teamName, 'Automated team creation description');

    await page.goto('/teams');
    await expect(page.getByText(teamName).first()).toBeVisible();
  });
});
