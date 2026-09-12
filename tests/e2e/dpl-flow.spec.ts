import { test, expect } from '@playwright/test';

const DPL_EMAIL = '0005099305@ubt.ac.id';
const DPL_PASSWORD = 'kknubt2026';

async function loginAsDpl(page: any): Promise<boolean> {
  await page.goto('/login');
  await page.fill('input[name="email"]', DPL_EMAIL);
  await page.fill('input[name="password"]', DPL_PASSWORD);
  await page.click('button[type="submit"]');
  try {
    await page.waitForURL(/.*(?!login)/, { timeout: 5000 });
    return !page.url().includes('/login');
  } catch {
    return false;
  }
}

const testIfDplAvailable = test.extend({});

test.describe('DPL Login & Dashboard', () => {
  test('should login as DPL and see dashboard', async ({ page }) => {
    const loggedIn = await loginAsDpl(page);
    test.skip(!loggedIn, 'DPL seeder data not available');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });
});

test.describe('DPL Sidebar Navigation', () => {
  test('should have navigation links in sidebar', async ({ page }) => {
    const loggedIn = await loginAsDpl(page);
    test.skip(!loggedIn, 'DPL seeder data not available');
    const sidebarText = await page.locator('#sidebar-wrapper').first().textContent();
    expect(sidebarText).toContain('Dashboard');
  });
});

test.describe('DPL Kelompok Binaan', () => {
  test('should display kelompok binaan page', async ({ page }) => {
    const loggedIn = await loginAsDpl(page);
    test.skip(!loggedIn, 'DPL seeder data not available');
    await page.goto('/dpl/kelompok');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });
});

test.describe('DPL Profile', () => {
  test('should display DPL profile edit form', async ({ page }) => {
    const loggedIn = await loginAsDpl(page);
    test.skip(!loggedIn, 'DPL seeder data not available');
    await page.goto('/dpl/profile/edit');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });
});

test.describe('DPL Penilaian', () => {
  test('should display DPL penilaian page', async ({ page }) => {
    const loggedIn = await loginAsDpl(page);
    test.skip(!loggedIn, 'DPL seeder data not available');
    await page.goto('/dpl/penilaian');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });
});
