import { test, expect } from '@playwright/test';

const ADMIN_EMAIL = 'admin@kknubt.ac.id';
const ADMIN_PASSWORD = 'password';

async function loginAsAdmin(page: any) {
  await page.goto('/login');
  await page.fill('input[name="email"]', ADMIN_EMAIL);
  await page.fill('input[name="password"]', ADMIN_PASSWORD);
  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/.*home/, { timeout: 10000 });
}

test.describe('WAR Admin Dashboard', () => {
  test('should display WAR admin index page', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/war');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
    await expect(page.locator('.section-header h1').first()).toContainText(/Plotting|WAR/);
  });

  test('should have create button', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/war');
    const createBtn = page.locator('a[href*="admin/war/create"]').first();
    await expect(createBtn).toBeVisible();
  });
});

test.describe('WAR Admin Create Session', () => {
  test('should display WAR create form', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/war/create');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
    await expect(page.locator('form:not(#logout-form)').first()).toBeVisible();
  });
});

test.describe('WAR Arena Access', () => {
  test('should be accessible to authenticated users', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/war');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('should show WAR lobby with status info', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/war');
    await expect(page.locator('.main-content').first()).toBeVisible();
  });
});
