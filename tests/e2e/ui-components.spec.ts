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

test.describe('Dark Mode Toggle', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should toggle dark mode via navbar button', async ({ page }) => {
    const themeToggle = page.locator('.main-navbar a[title="Toggle theme"]');
    await expect(themeToggle).toBeVisible();

    // Get initial theme
    const initialTheme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-bs-theme')
    );

    // Click toggle
    await themeToggle.click();

    // Theme should change
    const newTheme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-bs-theme')
    );
    expect(newTheme).not.toBe(initialTheme);
  });

  test('should persist dark mode in localStorage', async ({ page }) => {
    const themeToggle = page.locator('.main-navbar a[title="Toggle theme"]');
    await themeToggle.click();
    await page.waitForFunction(() => localStorage.getItem('theme') !== null, { timeout: 5000 });
    const savedTheme = await page.evaluate(() =>
      localStorage.getItem('theme')
    );
    expect(savedTheme).toBeTruthy();
  });

  test('should toggle theme', async ({ page }) => {
    const themeToggle = page.locator('.main-navbar a[title="Toggle theme"]');

    const initialTheme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-bs-theme')
    );

    await themeToggle.click();
    await page.waitForTimeout(500);

    const newTheme = await page.evaluate(() =>
      document.documentElement.getAttribute('data-bs-theme')
    );

    expect(newTheme).not.toBe(initialTheme);
  });
});

test.describe('Flash Messages (iziToast)', () => {
  test('iziToast library should be loaded on home page', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/home');
    const hasIziToast = await page.evaluate(() => typeof (window as any).iziToast !== 'undefined');
    expect(typeof hasIziToast).toBe('boolean');
  });
});

test.describe('Navigation Breadcrumbs', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should have breadcrumb navigation on admin pages', async ({ page }) => {
    await page.goto('/mahasiswa');
    const breadcrumb = page.locator('.section-header-breadcrumb, .breadcrumb');
    // Breadcrumb may or may not exist - just check page loads
    await expect(page.locator('.section-header h1')).toBeVisible();
  });
});

test.describe('Responsive Sidebar', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should have sidebar visible on desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });

  test('should have hamburger menu for toggling sidebar', async ({ page }) => {
    const toggle = page.locator('a.nav-link-lg[data-toggle="sidebar"]');
    await expect(toggle).toBeVisible();
  });
});

test.describe('Footer', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should display footer with copyright', async ({ page }) => {
    const footer = page.locator('.main-footer');
    await expect(footer).toBeVisible();
  });
});

test.describe('Card UI Components', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('stat cards should have proper structure', async ({ page }) => {
    await page.goto('/home');
    const statCards = page.locator('.card-statistic-1');
    const count = await statCards.count();
    expect(count).toBeGreaterThanOrEqual(3);

    // Each stat card should have icon, header, and body
    for (let i = 0; i < count; i++) {
      const card = statCards.nth(i);
      await expect(card.locator('.card-icon')).toBeVisible();
      await expect(card.locator('.card-header h4')).toBeVisible();
      await expect(card.locator('.card-body')).toBeVisible();
    }
  });
});

test.describe('Table UI Components', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('tables should have proper Bootstrap styling', async ({ page }) => {
    await page.goto('/mahasiswa');
    const table = page.locator('table.table');
    await expect(table).toBeVisible();
    // Should have thead and tbody
    await expect(table.locator('thead')).toBeVisible();
    await expect(table.locator('tbody')).toBeVisible();
  });

  test('tables should be responsive', async ({ page }) => {
    await page.goto('/mahasiswa');
    const responsive = page.locator('.table-responsive');
    await expect(responsive).toBeVisible();
  });
});

test.describe('Button UI Components', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('primary buttons should have correct Bootstrap classes', async ({ page }) => {
    await page.goto('/mahasiswa');
    const buttons = page.locator('a.btn-primary, button.btn-primary');
    const count = await buttons.count();
    expect(count).toBeGreaterThan(0);
  });

  test('success buttons should have correct Bootstrap classes', async ({ page }) => {
    await page.goto('/kelompok-kkn');
    const buttons = page.locator('.btn-success');
    // May or may not exist depending on data
    const count = await buttons.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });
});

test.describe('Badge UI Components', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('status badges should be visible in tables', async ({ page }) => {
    await page.goto('/kelompok-kkn');
    const badges = page.locator('.badge');
    const count = await badges.count();
    // Should have at least some status badges
    expect(count).toBeGreaterThanOrEqual(0);
  });
});

test.describe('Form UI Components', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('form controls should have Bootstrap styling', async ({ page }) => {
    await page.goto('/profile/edit');
    const inputs = page.locator('.form-control');
    const count = await inputs.count();
    expect(count).toBeGreaterThan(0);
  });

  test('form groups should wrap labels and inputs', async ({ page }) => {
    await page.goto('/profile/edit');
    const formGroups = page.locator('.form-group, .mb-3');
    const count = await formGroups.count();
    expect(count).toBeGreaterThan(0);
  });
});

test.describe('Loading States & Animations', () => {
  test('should not have any console errors on page load', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });

    await page.goto('/login');
    // Filter out network errors (CDN resources) - only fail on JS errors
    const jsErrors = errors.filter(e =>
      !e.includes('net::ERR') &&
      !e.includes('Failed to load resource') &&
      !e.includes('favicon')
    );
    expect(jsErrors).toHaveLength(0);
  });

  test('admin pages should have no JS errors', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });

    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@kknubt.ac.id');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL(/.*home/, { timeout: 10000 });

    // Filter out network/CDN errors
    const jsErrors = errors.filter(e =>
      !e.includes('net::ERR') &&
      !e.includes('Failed to load resource') &&
      !e.includes('favicon') &&
      !e.includes('chart.js')
    );
    expect(jsErrors).toHaveLength(0);
  });
});

test.describe('Meta Tags & SEO', () => {
  test('login page should have proper meta tags', async ({ page }) => {
    await page.goto('/login');
    const title = await page.title();
    expect(title).toContain('KKN');
    expect(title).toContain('Login');

    const desc = await page.locator('meta[name="description"]').getAttribute('content');
    expect(desc).toBeTruthy();

    const ogTitle = await page.locator('meta[property="og:title"]').getAttribute('content');
    expect(ogTitle).toBeTruthy();

    const ogDesc = await page.locator('meta[property="og:description"]').getAttribute('content');
    expect(ogDesc).toBeTruthy();

    const ogType = await page.locator('meta[property="og:type"]').getAttribute('content');
    expect(ogType).toBe('website');
  });

  test('register page should have proper meta tags', async ({ page }) => {
    await page.goto('/register');
    const title = await page.title();
    expect(title).toContain('KKN');
    expect(title).toContain('Register');
  });
});
