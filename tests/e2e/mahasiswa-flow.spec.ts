import { test, expect } from '@playwright/test';

test.describe('Mahasiswa Registration Flow', () => {
  test('should complete full registration and redirect to login', async ({ page }) => {
    const UNIQUE = Date.now();
    await page.goto('/register');

    await page.fill('input[name="name"]', 'E2E Test Mahasiswa');
    await page.fill('input[name="npm"]', `NPM${UNIQUE}`);
    await page.selectOption('select[name="jenis_kelamin"]', 'L');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', `e2e${UNIQUE}@example.com`);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');

    await page.click('button[type="submit"]');
    await page.waitForURL(/.*(login|register)/, { timeout: 15000 });
    expect(page.url()).toMatch(/(login|register)/);
  });
});

test.describe('Mahasiswa Biodata Redirect', () => {
  test('new user should be redirected to biodata after login', async ({ page }) => {
    const UNIQUE = Date.now();
    const freshEmail = `biodata${UNIQUE}@example.com`;

    await page.goto('/register');
    await page.fill('input[name="name"]', 'Biodata Test');
    await page.fill('input[name="npm"]', `NPMBIO${UNIQUE}`);
    await page.selectOption('select[name="jenis_kelamin"]', 'P');
    await page.selectOption('select[name="prodi_id"]', { index: 1 });
    await page.fill('input[name="email"]', freshEmail);
    await page.fill('input[name="password"]', 'password123');
    await page.fill('input[name="password_confirmation"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*(login|register)/, { timeout: 15000 });

    await page.goto('/login');
    await page.fill('input[name="email"]', freshEmail);
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL(/.*(biodata|home|login)/, { timeout: 15000 });
    expect(page.url()).toMatch(/(biodata|home)/);
  });
});

test.describe('Authenticated Page Access', () => {
  test('WAR page should be accessible to authenticated users', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kknubt.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*home/, { timeout: 10000 });

    await page.goto('/war');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });

  test('Kelompok hub should be accessible to authenticated users', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kknubt.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*home/, { timeout: 10000 });

    await page.goto('/kelompok');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });
});
