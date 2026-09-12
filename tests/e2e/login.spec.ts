import { test, expect } from '@playwright/test';

test.describe('Login', () => {
  test('should login with valid credentials and redirect to home', async ({ page }) => {
    await page.goto('/login');

    await page.fill('input[name="email"]', 'superadmin@kknubt.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');

    // Should redirect to home (or wherever superadmin goes)
    await expect(page).toHaveURL(/.*home/);
  });

  test('should show error for wrong password', async ({ page }) => {
    await page.goto('/login');

    await page.fill('input[name="email"]', 'superadmin@kknubt.id');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');

    // Should stay on login page
    await expect(page).toHaveURL(/.*login/);
  });

  test('should show error for non-existent email', async ({ page }) => {
    await page.goto('/login');

    await page.fill('input[name="email"]', 'nonexistent@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*login/);
  });

  test('login page should have register link', async ({ page }) => {
    await page.goto('/login');

    await expect(page.locator('a[href*="register"]')).toBeVisible();
  });
});
