import { test, expect } from '@playwright/test';

test.describe('Guest Access', () => {
  test('should redirect unauthenticated users from /home to /login', async ({ page }) => {
    await page.goto('/home');
    await expect(page).toHaveURL(/.*login/);
  });

  test('should redirect unauthenticated users from /kelompok-kkn to /login', async ({ page }) => {
    await page.goto('/kelompok-kkn');
    await expect(page).toHaveURL(/.*login/);
  });

  test('login page should render correctly', async ({ page }) => {
    await page.goto('/login');

    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('register page should render correctly', async ({ page }) => {
    await page.goto('/register');

    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="npm"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('root URL should show login form', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });
});
