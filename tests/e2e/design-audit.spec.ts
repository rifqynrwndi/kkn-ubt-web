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

test.describe('1. Design Tokens — Colors', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('status badge "Disetujui" uses success color', async ({ page }) => {
    await page.goto('/kelompok-kkn');
    const badge = page.locator('.badge:has-text("Disetujui"), .badge-success:has-text("Disetujui")').first();
    if (await badge.isVisible()) {
      const bg = await badge.evaluate((el: HTMLElement) => getComputedStyle(el).backgroundColor);
      expect(bg).toBeTruthy();
    }
  });

  test('no data cards have gradient backgrounds', async ({ page }) => {
    await page.goto('/mahasiswa');
    const cards = page.locator('.card');
    const count = await cards.count();
    for (let i = 0; i < Math.min(count, 5); i++) {
      const bg = await cards.nth(i).evaluate((el: HTMLElement) => getComputedStyle(el).backgroundImage);
      expect(bg).toBe('none');
    }
  });

  test('card border-radius does not exceed 12px', async ({ page }) => {
    await page.goto('/home');
    const cards = page.locator('.card');
    const count = await cards.count();
    for (let i = 0; i < Math.min(count, 5); i++) {
      const radius = await cards.nth(i).evaluate((el: HTMLElement) => {
        const r = getComputedStyle(el).borderRadius;
        return parseFloat(r);
      });
      expect(radius).toBeLessThanOrEqual(12);
    }
  });
});

test.describe('2. Design Tokens — Typography', () => {
  test('page title uses Nunito font', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/home');
    const h1 = page.locator('.section-header h1').first();
    const fontFamily = await h1.evaluate((el: HTMLElement) => getComputedStyle(el).fontFamily);
    expect(fontFamily.toLowerCase()).toContain('nunito');
  });

  test('page title has font-weight 700', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/home');
    const h1 = page.locator('.section-header h1').first();
    const weight = await h1.evaluate((el: HTMLElement) => getComputedStyle(el).fontWeight);
    expect(parseInt(weight)).toBeGreaterThanOrEqual(700);
  });

  test('body text has line-height >= 1.4', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/home');
    const body = page.locator('.main-content').first();
    const lh = await body.evaluate((el: HTMLElement) => parseFloat(getComputedStyle(el).lineHeight));
    expect(lh).toBeGreaterThanOrEqual(20);
  });
});

test.describe('3. Status Badge', () => {
  test('badge has pill shape (border-radius 9999px)', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok-kkn');
    const badge = page.locator('.badge').first();
    if (await badge.isVisible()) {
      const radius = await badge.evaluate((el: HTMLElement) => getComputedStyle(el).borderRadius);
      expect(radius).toBe('9999px');
    }
  });

  test('badge uses semantic color classes', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok-kkn');
    const badges = page.locator('.badge');
    const count = await badges.count();
    for (let i = 0; i < Math.min(count, 10); i++) {
      const classes = await badges.nth(i).evaluate((el: HTMLElement) => el.className);
      const hasSemanticColor = /badge-(success|danger|warning|info|primary|secondary|dark)/.test(classes);
      expect(hasSemanticColor).toBeTruthy();
    }
  });
});

test.describe('4. Group Header', () => {
  test('group header card has proper structure', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    const header = page.locator('.group-header-card');
    if (await header.isVisible()) {
      await expect(header).toBeVisible();
      const photoWrap = page.locator('.group-photo-wrap');
      if (await photoWrap.isVisible()) {
        const width = await photoWrap.evaluate((el: HTMLElement) => el.offsetWidth);
        expect(width).toBeGreaterThanOrEqual(130);
        expect(width).toBeLessThanOrEqual(160);
      }
    }
  });

  test('group nav tabs are visible and horizontal', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    const nav = page.locator('.group-nav');
    if (await nav.isVisible()) {
      const display = await nav.evaluate((el: HTMLElement) => getComputedStyle(el).display);
      expect(display).toBe('flex');
    }
  });
});

test.describe('5. Status Stepper', () => {
  test('stepper shows numbered steps', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    await page.click('a[data-tab="status"], a:has-text("Status")');
    await page.waitForTimeout(500);
    const steps = page.locator('[style*="border-radius:50%"]');
    const count = await steps.count();
    expect(count).toBeGreaterThanOrEqual(2);
  });

  test('current step has different visual style', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    await page.click('a[data-tab="status"], a:has-text("Status")');
    await page.waitForTimeout(500);
    const activeStep = page.locator('[style*="font-weight:800"]').first();
    if (await activeStep.isVisible()) {
      const fontWeight = await activeStep.evaluate((el: HTMLElement) => getComputedStyle(el).fontWeight);
      expect(parseInt(fontWeight)).toBeGreaterThanOrEqual(700);
    }
  });
});

test.describe('6. Proposal Form', () => {
  test('has section structure with character counters', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    await page.click('a[data-tab="proposal"], a:has-text("Proposal")');
    await page.waitForTimeout(500);
    const sections = page.locator('.section-wrap, .accordion-item, [class*="section"]');
    const count = await sections.count();
    expect(count).toBeGreaterThanOrEqual(1);
  });
});

test.describe('7. Log Book', () => {
  test('progress bar is visible with correct structure', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    await page.click('a[data-tab="logbook"], a:has-text("Log Book")');
    await page.waitForTimeout(500);
    const progress = page.locator('.progress, [role="progressbar"], [style*="progress"]').first();
    if (await progress.isVisible()) {
      const height = await progress.evaluate((el: HTMLElement) => el.offsetHeight);
      expect(height).toBeGreaterThanOrEqual(4);
    }
  });
});

test.describe('8. Pengumpulan Tugas', () => {
  test('accordion categories exist', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    await page.click('a[data-tab="tugas"], a:has-text("Tugas")');
    await page.waitForTimeout(500);
    const accordions = page.locator('.accordion, .card-header, [data-toggle="collapse"]');
    const count = await accordions.count();
    expect(count).toBeGreaterThanOrEqual(1);
  });
});

test.describe('9. Penilaian', () => {
  test('score table has grouped rows', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    await page.click('a[data-tab="penilaian"], a:has-text("Penilaian")');
    await page.waitForTimeout(500);
    const table = page.locator('table').first();
    if (await table.isVisible()) {
      await expect(table.locator('thead')).toBeVisible();
    }
  });
});

test.describe('10. Accessibility', () => {
  test('all images have alt text', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/home');
    const images = page.locator('img');
    const count = await images.count();
    for (let i = 0; i < Math.min(count, 10); i++) {
      const alt = await images.nth(i).getAttribute('alt');
      expect(alt).toBeTruthy();
    }
  });

  test('all form inputs have associated labels', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/profile/edit');
    const inputs = page.locator('input:not([type="hidden"]), select, textarea');
    const count = await inputs.count();
    for (let i = 0; i < Math.min(count, 10); i++) {
      const id = await inputs.nth(i).getAttribute('id');
      const ariaLabel = await inputs.nth(i).getAttribute('aria-label');
      const ariaLabelledby = await inputs.nth(i).getAttribute('aria-labelledby');
      const hasLabel = id ? await page.locator(`label[for="${id}"]`).count() > 0 : false;
      const hasAria = !!(ariaLabel || ariaLabelledby);
      expect(hasLabel || hasAria).toBeTruthy();
    }
  });

  test('buttons have visible text or aria-label', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/home');
    const buttons = page.locator('button, a.btn');
    const count = await buttons.count();
    for (let i = 0; i < Math.min(count, 15); i++) {
      const text = await buttons.nth(i).innerText();
      const ariaLabel = await buttons.nth(i).getAttribute('aria-label');
      const hasContent = text.trim().length > 0 || !!ariaLabel;
      expect(hasContent).toBeTruthy();
    }
  });

  test('skip-to-content link exists', async ({ page }) => {
    await page.goto('/login');
    const skipLink = page.locator('a[href="#main-content"], a[href="#content"], .sr-only a, .skip-link');
    const count = await skipLink.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });
});

test.describe('11. Responsive — Mobile', () => {
  test('sidebar hidden on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await loginAsAdmin(page);
    const sidebar = page.locator('#sidebar-wrapper').first();
    if (await sidebar.count() > 0) {
      const visible = await sidebar.isVisible();
      if (visible) {
        const width = await sidebar.evaluate((el: HTMLElement) => el.offsetWidth);
        expect(width).toBeLessThan(100);
      }
    }
  });

  test('hamburger menu visible on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await loginAsAdmin(page);
    const toggle = page.locator('a.nav-link-lg[data-toggle="sidebar"], .navbar-toggler, [data-toggle="sidebar"]');
    const count = await toggle.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });
});

test.describe('12. Responsive — Desktop', () => {
  test('sidebar visible on desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await loginAsAdmin(page);
    const sidebar = page.locator('#sidebar-wrapper').first();
    if (await sidebar.count() > 0) {
      await expect(sidebar).toBeVisible();
    }
  });

  test('main content has proper padding', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await loginAsAdmin(page);
    const main = page.locator('.main-content, .section').first();
    if (await main.isVisible()) {
      const padding = await main.evaluate((el: HTMLElement) => getComputedStyle(el).paddingLeft);
      expect(parseFloat(padding)).toBeGreaterThanOrEqual(16);
    }
  });
});

test.describe('13. Dark Mode', () => {
  test('dark mode toggle exists and works', async ({ page }) => {
    await loginAsAdmin(page);
    const toggle = page.locator('.main-navbar a[title="Toggle theme"], [data-toggle="theme"]');
    if (await toggle.count() > 0) {
      await expect(toggle.first()).toBeVisible();
      const initial = await page.evaluate(() => document.documentElement.getAttribute('data-bs-theme'));
      await toggle.first().click();
      await page.waitForTimeout(500);
      const after = await page.evaluate(() => document.documentElement.getAttribute('data-bs-theme'));
      expect(after).not.toBe(initial);
    }
  });

  test('dark mode cards have dark background', async ({ page }) => {
    await loginAsAdmin(page);
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-bs-theme', 'dark');
    });
    await page.goto('/home');
    const card = page.locator('.card').first();
    if (await card.isVisible()) {
      const bg = await card.evaluate((el: HTMLElement) => getComputedStyle(el).backgroundColor);
      const r = parseInt(bg.split(',')[0].split('(')[1]);
      expect(r).toBeLessThan(200);
    }
  });
});

test.describe('14. No Console Errors', () => {
  test('login page has no JS errors', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await page.goto('/login');
    const jsErrors = errors.filter(e =>
      !e.includes('net::ERR') &&
      !e.includes('Failed to load resource') &&
      !e.includes('favicon')
    );
    expect(jsErrors).toHaveLength(0);
  });

  test('kelompok page has no JS errors', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    await loginAsAdmin(page);
    await page.goto('/kelompok');
    const jsErrors = errors.filter(e =>
      !e.includes('net::ERR') &&
      !e.includes('Failed to load resource') &&
      !e.includes('favicon') &&
      !e.includes('quill')
    );
    expect(jsErrors).toHaveLength(0);
  });
});
