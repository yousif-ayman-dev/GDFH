import { test, expect } from '@playwright/test';
import { login, setupErrorListeners } from './helpers/test-helpers';

test.describe('Admin Dashboard Suite', () => {
  let errorDetector: ReturnType<typeof setupErrorListeners>;

  test.beforeEach(async ({ page }) => {
    errorDetector = setupErrorListeners(page);
    // Login as system admin
    await login(page, process.env.E2E_ADMIN_EMAIL || 'admin@gdfh.edu', 'password');
  });

  test.afterEach(async () => {
    errorDetector.checkErrors();
  });

  test('admin can access the admin dashboard overview', async ({ page }) => {
    await page.goto('/admin', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/admin/);
    await expect(page.locator('body')).toContainText(/لوحة تحكم|مدير النظام|إدارة|Admin/i);
  });

  test('admin dashboard shows system stats', async ({ page }) => {
    await page.goto('/admin', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#admin-projects-link')).toBeVisible();
    await expect(page.locator('#admin-users-link')).toBeVisible();
  });

  test('admin can navigate to user management', async ({ page }) => {
    await page.goto('/admin/users', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/admin\/users/);
    await expect(page.locator('body')).toContainText(/حسابات|المستخدمين|إدارة/i);
  });

  test('admin can navigate to project management', async ({ page }) => {
    await page.goto('/admin/projects', { waitUntil: 'domcontentloaded' });
    await expect(page).toHaveURL(/\/admin\/projects/);
    await expect(page.locator('body')).toContainText(/مشاريع|المشاريع/i);
  });

  test('admin users page shows search field', async ({ page }) => {
    await page.goto('/admin/users', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#admin-user-search')).toBeVisible();
  });

  test('admin users table is present', async ({ page }) => {
    await page.goto('/admin/users', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#admin-users-table')).toBeVisible();
  });

  test('admin projects table is present', async ({ page }) => {
    await page.goto('/admin/projects', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('#admin-projects-table')).toBeVisible();
  });
});

test.describe('Admin Access Control Suite', () => {
  test('regular client cannot access admin panel', async ({ page }) => {
    await login(page, 'client@gdfh.edu', 'password');
    const response = await page.goto('/admin', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(403);
  });

  test('regular freelancer cannot access admin panel', async ({ page }) => {
    await login(page, 'freelancer1@gdfh.edu', 'password');
    const response = await page.goto('/admin', { waitUntil: 'domcontentloaded' });
    expect(response?.status()).toBe(403);
  });
});
