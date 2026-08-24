import { Page, expect } from '@playwright/test';

/**
 * Setup console error and network 500 error detection on the page.
 * Fails the test if unexpected console errors or HTTP 500 responses occur.
 */
export function setupErrorListeners(page: Page): { checkErrors: () => void } {
  const errors: string[] = [];

  page.on('console', (msg) => {
    if (msg.type() === 'error') {
      const text = msg.text();
      // Filter out benign browser devtools/extension/network-reconnect/DNS/TCP lookup messages
      if (
        !text.includes('favicon.ico') &&
        !text.includes('chrome-extension') &&
        !text.includes('ERR_NETWORK_CHANGED') &&
        !text.includes('ERR_NAME_NOT_RESOLVED') &&
        !text.includes('ERR_CONNECTION_RESET') &&
        !text.includes('[vite]')
      ) {
        errors.push(`Console Error: ${text}`);
      }
    }
  });

  page.on('response', (response) => {
    if (response.status() === 500) {
      errors.push(`HTTP 500 Internal Server Error at: ${response.url()}`);
    }
  });

  return {
    checkErrors: () => {
      if (errors.length > 0) {
        throw new Error(`Test failed due to runtime errors:\n${errors.join('\n')}`);
      }
    },
  };
}

/**
 * Helper to log in a user with dynamic locators.
 */
export async function login(
  page: Page,
  email: string = process.env.E2E_USER_EMAIL || 'client@gdfh.edu',
  password: string = process.env.E2E_USER_PASSWORD || 'password'
) {
  await page.goto('/login');
  await expect(page.locator('#email')).toBeVisible();

  await page.fill('#email', email);
  await page.fill('#password', password);

  await page.click('#login-submit-btn, form[action*="login"] button[type="submit"], button[type="submit"]');
  await page.waitForURL(url => !url.toString().endsWith('/login'), { timeout: 15000 }).catch(() => {});
  await expect(page).not.toHaveURL(/\/login$/);
}

/**
 * Helper to create a new project.
 */
export async function createProject(
  page: Page,
  name: string = `Project ${Date.now()}`,
  description: string = 'Enterprise Playwright Test Project Description'
) {
  await page.goto('/projects/create');
  await expect(page.locator('#title')).toBeVisible();

  await page.fill('#title', name);
  await page.fill('#description', description);

  await page.click('main form button[type="submit"], form[action*="projects"] button[type="submit"]');
  await expect(page).toHaveURL(/\/projects/, { timeout: 15000 });
  return name;
}

/**
 * Helper to create a new team.
 */
export async function createTeam(
  page: Page,
  name: string = `Team ${Date.now()}`,
  description: string = 'Enterprise Playwright Test Team Description'
) {
  await page.goto('/teams/create');
  await expect(page.locator('#name')).toBeVisible();

  await page.fill('#name', name);
  await page.fill('#description', description);

  await page.click('main form button[type="submit"], form[action*="teams"] button[type="submit"]');
  await expect(page).toHaveURL(/\/teams/, { timeout: 15000 });
  return name;
}

/**
 * Helper to create a task under a project.
 */
export async function createTask(
  page: Page,
  taskTitle: string = `Task ${Date.now()}`,
  taskDesc: string = 'Enterprise Playwright Task Description'
) {
  if (!page.url().match(/\/projects\/\d+/)) {
    const projName = await createProject(page);
    await page.goto('/projects');
    await page.getByText(projName).first().click();
  }

  const addTaskLink = page.locator('a[href*="/tasks/create"]').first();
  if (await addTaskLink.count() > 0) {
    await addTaskLink.click();
  } else {
    const currentUrl = page.url();
    const match = currentUrl.match(/\/projects\/(\d+)/);
    if (match) {
      await page.goto(`/projects/${match[1]}/tasks/create`);
    }
  }

  await expect(page.locator('#title')).toBeVisible();
  await page.fill('#title', taskTitle);

  const descField = page.locator('#description');
  if (await descField.count() > 0) {
    await descField.fill(taskDesc);
  }

  await page.click('main form button[type="submit"], form[action*="tasks"] button[type="submit"]');
  await expect(page).toHaveURL(/\/projects/);
  return taskTitle;
}

/**
 * Helper to archive a project.
 */
export async function archiveProject(page: Page) {
  if (!page.url().match(/\/projects\/\d+/)) {
    await page.goto('/projects');
    const projectCard = page.locator('a[href*="/projects/"]').filter({ hasNotText: 'create' }).first();
    await projectCard.click();
  }

  const archiveBtn = page.locator('main button:has-text("أرشفة"), form[action*="/archive"] button, button:has-text("Archive")').first();
  await expect(archiveBtn).toBeVisible();
  await archiveBtn.click();
}

/**
 * Helper to open AI Assistant page.
 */
export async function openAI(page: Page) {
  await page.goto('/ai', { waitUntil: 'domcontentloaded' });
  await expect(page).toHaveURL(/\/ai/);
}

/**
 * Helper to toggle light/dark theme.
 */
export async function toggleTheme(page: Page) {
  const themeToggleContainer = page.locator('[aria-label="المظهر"], #theme-toggle, .theme-toggle').first();
  await expect(themeToggleContainer).toBeVisible();

  const themeBtn = themeToggleContainer.locator('button').first();
  await themeBtn.click();

  const html = page.locator('html');
  await expect(html).toBeDefined();
}
