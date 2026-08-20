import { test, expect } from '@playwright/test';
import { login, createProject, createTask, setupErrorListeners } from './helpers/test-helpers';

test.describe('Tasks Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should create a task under a project dynamically', async ({ page }) => {
    const projectName = `Task Container Project ${Date.now()}`;
    await createProject(page, projectName);

    await page.goto('/projects');
    await page.getByText(projectName).first().click();

    const taskTitle = `E2E Dynamic Task ${Date.now()}`;
    await createTask(page, taskTitle, 'Automated task creation description');

    await expect(page).toHaveURL(/\/projects/);
  });
});
