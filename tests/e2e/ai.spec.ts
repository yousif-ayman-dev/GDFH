import { test, expect } from '@playwright/test';
import { login, openAI, setupErrorListeners } from './helpers/test-helpers';

test.describe('AI Assistant Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    await login(page);
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('should open AI Assistant view using helper', async ({ page }) => {
    await openAI(page);
    await expect(page.locator('body')).toContainText(/الذكاء الاصطناعي|مساعد|AI/i);
  });
});
