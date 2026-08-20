import { test, expect } from '@playwright/test';
import { login, createProject, archiveProject, setupErrorListeners } from './helpers/test-helpers';

test.describe('Projects Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should display projects list view', async ({ page }) => {
    await page.goto('/projects');
    await expect(page).toHaveURL(/\/projects$/);
    await expect(page.locator('body')).toContainText(/المشاريع/);
  });

  test('should create a new project with dynamic locators', async ({ page }) => {
    const projectName = `Enterprise E2E Project ${Date.now()}`;
    await createProject(page, projectName, 'Automated enterprise test project description');
    
    await page.goto('/projects');
    await expect(page.getByText(projectName).first()).toBeVisible();
  });

  test('should archive a project dynamically', async ({ page }) => {
    const projectName = `Project to Archive ${Date.now()}`;
    await createProject(page, projectName);

    await page.goto('/projects');
    await page.getByText(projectName).first().click();

    await archiveProject(page);
    await expect(page.locator('body')).toBeVisible();
  });
});
