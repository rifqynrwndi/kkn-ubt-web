import { test, expect } from '@playwright/test';

test.describe('Register', () => {
  test('should register a new user and redirect to login', async ({ page }) => {
    await page.goto('/register');

    // Fill form
    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="npm"]', '1234567890');
    await page.selectOption('select[name="jenis_kelamin"]', 'L');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', `test${Date.now()}@example.com`);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');

    // Submit
    await page.click('button[type="submit"]');

    // Should redirect to login
    await expect(page).toHaveURL(/.*login/);

    // Success toast should appear
    await expect(page.locator('.iziToast')).toBeVisible({ timeout: 5000 });
  });

  test('should show validation error for duplicate email', async ({ page }) => {
    await page.goto('/register');

    // Use existing email
    await page.fill('input[name="name"]', 'Dup User');
    await page.fill('input[name="npm"]', '9999999999');
    await page.selectOption('select[name="jenis_kelamin"]', 'P');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', 'superadmin@kknubt.id');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');

    await page.click('button[type="submit"]');

    // Should stay on register with error
    await expect(page).toHaveURL(/.*register/);
  });

  test('should show validation error for mismatched password', async ({ page }) => {
    await page.goto('/register');

    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="npm"]', '1234567891');
    await page.selectOption('select[name="jenis_kelamin"]', 'L');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', `test${Date.now()}@example.com`);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password456');

    await page.click('button[type="submit"]');

    // Should stay on register page
    await expect(page).toHaveURL(/.*register/);
  });
});
