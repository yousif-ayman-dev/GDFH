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

  // Requirement #15 — AI Analyze button present on project create form
  test('AI analyze project button is visible on project create form', async ({ page }) => {
    await page.goto('/projects/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#ai-analyze-project-btn')).toBeVisible();
  });

  test('AI analyze project button activates after description is filled', async ({ page }) => {
    await page.goto('/projects/create', { waitUntil: 'domcontentloaded' });
    await page.fill('#description', 'بناء منصة تجارة إلكترونية متكاملة تتضمن إدارة المنتجات والطلبات والمدفوعات');
    const btn = page.locator('#ai-analyze-project-btn');
    await expect(btn).toBeVisible();
    await expect(btn).toBeEnabled();
  });
});

test.describe('AI Feature Recommended Projects (Freelancer)', () => {
  test('AI recommended projects widget is visible for freelancers', async ({ page }) => {
    await login(page, process.env.E2E_FREELANCER_EMAIL || 'freelancer1@gdfh.edu', 'password');
    await page.goto('/marketplace', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#ai-recommended-projects-widget')).toBeVisible();
    await expect(page.locator('#ai-load-recommended-btn')).toBeVisible();
  });
});
