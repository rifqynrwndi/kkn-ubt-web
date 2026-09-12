import { test, expect } from '@playwright/test';

const UNIQUE = Date.now();

test.describe('Register', () => {
  test('should register a new user and redirect to login', async ({ page }) => {
    await page.goto('/register');

    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="npm"]', `NPM${UNIQUE}`);
    await page.selectOption('select[name="jenis_kelamin"]', 'L');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', `test${UNIQUE}@example.com`);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');

    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*login/, { timeout: 10000 });
    await expect(page.locator('.iziToast')).toBeVisible({ timeout: 5000 });
  });

  test('should show validation error for duplicate email', async ({ page }) => {
    await page.goto('/register');

    await page.fill('input[name="name"]', 'Dup User');
    await page.fill('input[name="npm"]', `DUP${UNIQUE}`);
    await page.selectOption('select[name="jenis_kelamin"]', 'P');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', 'admin@kknubt.ac.id');
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');

    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*register/);
  });

  test('should show validation error for mismatched password', async ({ page }) => {
    await page.goto('/register');

    await page.fill('input[name="name"]', 'Test User');
    await page.fill('input[name="npm"]', `PWD${UNIQUE}`);
    await page.selectOption('select[name="jenis_kelamin"]', 'L');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', `pwd${UNIQUE}@example.com`);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password456');

    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/.*register/);
  });
});
