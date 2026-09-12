import { test, expect } from '@playwright/test';

test.describe('Auth Layout UI/UX', () => {
  test.describe('Login Page', () => {
    test.beforeEach(async ({ page }) => {
      await page.goto('/login');
    });

    test('should display split-screen layout with banner and form', async ({ page }) => {
      await expect(page.locator('.auth-wrapper')).toBeVisible();
      await expect(page.locator('.auth-banner')).toBeVisible();
      await expect(page.locator('.auth-form-side')).toBeVisible();
    });

    test('should display UBT branding on banner', async ({ page }) => {
      const banner = page.locator('.auth-banner-content');
      await expect(banner).toBeVisible();
      await expect(banner.locator('h2')).toContainText('KKN UBT');
      await expect(banner.locator('p')).toContainText('Universitas Borneo Tarakan');
      await expect(banner.locator('img[alt="Universitas Borneo Tarakan"]')).toBeVisible();
    });

    test('should display auth card with correct heading', async ({ page }) => {
      const card = page.locator('.auth-card');
      await expect(card).toBeVisible();
      await expect(card.locator('.auth-card-header h3')).toContainText('Selamat Datang');
      await expect(card.locator('.auth-card-header p')).toContainText('Login untuk mengakses');
    });

    test('should have all required form fields', async ({ page }) => {
      await expect(page.locator('input[name="email"]')).toBeVisible();
      await expect(page.locator('input[name="password"]')).toBeVisible();
      await expect(page.locator('button[type="submit"]')).toBeVisible();
    });

    test('should have correct placeholders', async ({ page }) => {
      await expect(page.locator('input[name="email"]')).toHaveAttribute('placeholder', 'Masukkan email');
      await expect(page.locator('input[name="password"]')).toHaveAttribute('placeholder', 'Masukkan password');
    });

    test('should have login button with icon and correct text', async ({ page }) => {
      const btn = page.locator('button[type="submit"]');
      await expect(btn).toContainText('Login');
      await expect(btn.locator('i.fas.fa-sign-in-alt')).toBeVisible();
    });

    test('should have "Lupa Password?" link', async ({ page }) => {
      const link = page.locator('.auth-card a:has-text("Lupa Password")');
      await expect(link).toBeVisible();
      await expect(link).toHaveAttribute('href', /password\/reset/);
    });

    test('should have register link at bottom', async ({ page }) => {
      await expect(page.locator('text=Belum punya akun?')).toBeVisible();
      const registerLink = page.locator('a[href*="register"]');
      await expect(registerLink).toBeVisible();
      await expect(registerLink).toContainText('Register');
    });

    test('should have page title with correct branding', async ({ page }) => {
      await expect(page).toHaveTitle(/KKN Universitas Borneo Tarakan.*Login/);
    });

    test('should have proper meta tags', async ({ page }) => {
      const desc = await page.locator('meta[name="description"]').getAttribute('content');
      expect(desc).toContain('KKN Universitas Borneo Tarakan');
    });

    test('should have favicon', async ({ page }) => {
      const favicon = page.locator('link[rel="icon"]');
      await expect(favicon).toHaveAttribute('type', 'image/png');
    });

    test('form inputs should have proper focus styling via CSS', async ({ page }) => {
      const email = page.locator('input[name="email"]');
      await email.click();
      await expect(email).toBeFocused();
    });

    test('should have form labels above inputs', async ({ page }) => {
      const emailLabel = page.locator('label[for="email"]');
      await expect(emailLabel).toContainText('Email');
      const passwordLabel = page.locator('label[for="password"]');
      await expect(passwordLabel).toContainText('Password');
    });
  });

  test.describe('Register Page', () => {
    test.beforeEach(async ({ page }) => {
      await page.goto('/register');
    });

    test('should display auth card with "Daftar Akun" heading', async ({ page }) => {
      const card = page.locator('.auth-card');
      await expect(card).toBeVisible();
      await expect(card.locator('.auth-card-header h3')).toContainText('Daftar Akun');
      await expect(card.locator('.auth-card-header p')).toContainText('Buat akun untuk mengikuti program KKN');
    });

    test('should have all registration form fields', async ({ page }) => {
      await expect(page.locator('input[name="name"]')).toBeVisible();
      await expect(page.locator('input[name="npm"]')).toBeVisible();
      await expect(page.locator('select[name="jenis_kelamin"]')).toBeVisible();
      await expect(page.locator('select[name="prodi_id"]')).toBeVisible();
      await expect(page.locator('input[name="email"]')).toBeVisible();
      await expect(page.locator('input[name="password"]')).toBeVisible();
      await expect(page.locator('input[name="password_confirmation"]')).toBeVisible();
    });

    test('should have correct field labels', async ({ page }) => {
      await expect(page.locator('label[for="name"]')).toContainText('Nama Lengkap');
      await expect(page.locator('label[for="npm"]')).toContainText('NPM');
      await expect(page.locator('label[for="jenis_kelamin"]')).toContainText('Jenis Kelamin');
      await expect(page.locator('label[for="prodi_id"]')).toContainText('Program Studi');
      await expect(page.locator('label[for="email"]')).toContainText('Email');
      await expect(page.locator('label[for="password"]')).toContainText('Password');
      await expect(page.locator('label[for="password-confirm"]')).toContainText('Konfirmasi Password');
    });

    test('should have correct placeholders', async ({ page }) => {
      await expect(page.locator('input[name="name"]')).toHaveAttribute('placeholder', 'Masukkan nama lengkap');
      await expect(page.locator('input[name="npm"]')).toHaveAttribute('placeholder', 'NPM');
      await expect(page.locator('input[name="email"]')).toHaveAttribute('placeholder', 'Masukkan email');
      await expect(page.locator('input[name="password"]')).toHaveAttribute('placeholder', 'Min. 8 karakter');
      await expect(page.locator('input[name="password_confirmation"]')).toHaveAttribute('placeholder', 'Ulangi password');
    });

    test('should have gender select options', async ({ page }) => {
      const select = page.locator('select[name="jenis_kelamin"]');
      await expect(select.locator('option')).toHaveCount(3); // placeholder + L + P
      await expect(select.locator('option[value="L"]')).toContainText('Laki-laki');
      await expect(select.locator('option[value="P"]')).toContainText('Perempuan');
    });

    test('should have prodi select with options from DB', async ({ page }) => {
      const select = page.locator('select[name="prodi_id"]');
      const optionCount = await select.locator('option').count();
      expect(optionCount).toBeGreaterThan(1); // At least one prodi from seeder
    });

    test('should have register button with icon', async ({ page }) => {
      const btn = page.locator('button[type="submit"]');
      await expect(btn).toContainText('Daftar');
      await expect(btn.locator('i.fas.fa-user-plus')).toBeVisible();
    });

    test('should have login link at bottom', async ({ page }) => {
      await expect(page.locator('text=Sudah punya akun?')).toBeVisible();
      const loginLink = page.locator('a[href*="login"]');
      await expect(loginLink).toContainText('Login');
    });

    test('should navigate to login when clicking login link', async ({ page }) => {
      await page.locator('.auth-card a[href*="login"]').click();
      await expect(page).toHaveURL(/.*login/);
    });

    test('should have split-screen layout like login', async ({ page }) => {
      await expect(page.locator('.auth-wrapper')).toBeVisible();
      await expect(page.locator('.auth-banner')).toBeVisible();
      await expect(page.locator('.auth-form-side')).toBeVisible();
    });

    test('should have proper NPM and password side-by-side layout', async ({ page }) => {
      const npmCol = page.locator('.col-md-6.mb-3').filter({ has: page.locator('input[name="npm"]') });
      const genderCol = page.locator('.col-md-6.mb-3').filter({ has: page.locator('select[name="jenis_kelamin"]') });
      await expect(npmCol).toBeVisible();
      await expect(genderCol).toBeVisible();
    });

    test('should have password and confirmation side-by-side', async ({ page }) => {
      const pwCol = page.locator('.col-md-6.mb-3').filter({ has: page.locator('input[name="password"]') });
      const confirmCol = page.locator('.col-md-6.mb-3').filter({ has: page.locator('input[name="password_confirmation"]') });
      await expect(pwCol).toBeVisible();
      await expect(confirmCol).toBeVisible();
    });
  });

  test.describe('Responsive Layout', () => {
    test('should hide banner on mobile viewport', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 812 });
      await page.goto('/login');
      const banner = page.locator('.auth-banner');
      await expect(banner).toBeHidden();
    });

    test('should still show form on mobile', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 812 });
      await page.goto('/login');
      await expect(page.locator('.auth-card')).toBeVisible();
      await expect(page.locator('input[name="email"]')).toBeVisible();
    });

    test('should show banner on desktop', async ({ page }) => {
      await page.setViewportSize({ width: 1440, height: 900 });
      await page.goto('/login');
      await expect(page.locator('.auth-banner')).toBeVisible();
    });
  });
});
