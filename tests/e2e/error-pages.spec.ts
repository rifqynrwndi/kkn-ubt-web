import { test, expect } from '@playwright/test';

test.describe('Error Pages', () => {
  test('413 page route exists', async ({ page }) => {
    const response = await page.goto('/errors/413');
    expect(response?.status()).toBeLessThan(500);
  });

  test('non-existent page should show 404', async ({ page }) => {
    const response = await page.goto('/this-page-does-not-exist-12345');
    expect(response?.status()).toBe(404);
  });
});
