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

test.describe('App Layout (Authenticated)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should display sidebar with branding', async ({ page }) => {
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
    await expect(page.locator('body')).toContainText('KKN UBT');
  });

  test('should display top navbar', async ({ page }) => {
    const navbar = page.locator('.main-navbar');
    await expect(navbar).toBeVisible();
  });

  test('should have hamburger menu toggle', async ({ page }) => {
    const toggle = page.locator('a.nav-link-lg[data-toggle="sidebar"]');
    await expect(toggle).toBeVisible();
    await expect(toggle.locator('i.fas.fa-bars')).toBeVisible();
  });

  test('should have theme toggle in navbar', async ({ page }) => {
    const themeToggle = page.locator('.main-navbar a[title="Toggle theme"]');
    await expect(themeToggle).toBeVisible();
  });

  test('should have user dropdown with avatar', async ({ page }) => {
    const dropdown = page.locator('.nav-link-user');
    await expect(dropdown).toBeVisible();
    await expect(dropdown.locator('img.rounded-circle')).toBeVisible();
    await expect(dropdown).toContainText('Hi,');
  });

  test('should have main content area', async ({ page }) => {
    await expect(page.locator('.main-content')).toBeVisible();
  });

  test('should have footer', async ({ page }) => {
    await expect(page.locator('.main-footer')).toBeVisible();
  });

  test('should show "Dashboard Admin" heading', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Dashboard Admin');
  });

  test('user dropdown should show welcome message and options', async ({ page }) => {
    await page.click('.nav-link-user');
    const dropdown = page.locator('.dropdown-menu.show');
    await expect(dropdown).toBeVisible();
    await expect(dropdown.locator('.dropdown-title')).toContainText('Welcome,');
    await expect(dropdown.locator('.edit-profile')).toContainText('Edit Profile');
    await expect(dropdown.locator('text=Logout')).toBeVisible();
  });
});

test.describe('Admin Sidebar Navigation', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should display all superadmin menu sections', async ({ page }) => {
    const sidebarText = await page.locator('.sidebar-menu').first().textContent();
    expect(sidebarText).toContain('Dashboard');
    expect(sidebarText).toContain('Hak Akses');
    expect(sidebarText).toContain('Data Mahasiswa');
    expect(sidebarText).toContain('Gelombang');
    expect(sidebarText).toContain('Kelompok');
  });

  test('should have Dashboard link', async ({ page }) => {
    await expect(page.locator('.sidebar-menu a.nav-link[href*="home"]')).toBeVisible();
  });

  test('should have Hak Akses link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="hakakses"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Hak Akses');
  });

  test('should have Data Mahasiswa link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="mahasiswa"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Data Mahasiswa');
  });

  test('should have DPL link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="pembimbing-lapangan"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Dosen Pembimbing Lapangan');
  });

  test('should have Gelombang KKN link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="gelombang"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Gelombang KKN');
  });

  test('should have Fakultas & Prodi link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="fakultas-prodi"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Fakultas & Prodi');
  });

  test('should have Verifikasi Dokumen link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="verifikasi-dokumen"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Verifikasi Dokumen');
  });

  test('should have Data Desa link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="desa"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Data Desa');
  });

  test('should have Kelompok KKN link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="kelompok-kkn"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Kelompok KKN');
  });

  test('should have Plotting Kelompok (WAR) link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="admin/war"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Plotting Kelompok');
  });

  test('should have Tugas Kelompok link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="admin/tugas"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Tugas Kelompok');
  });

  test('should have Penilaian link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="admin/penilaian"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Penilaian');
  });

  test('should have Notifikasi link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="notifications"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Notifikasi');
  });

  test('should have Profil link in Akun section', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="profile/edit"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Profil');
  });

  test('should have Ubah Password link', async ({ page }) => {
    const link = page.locator('a.nav-link[href*="profile/change-password"]');
    await expect(link).toBeVisible();
    await expect(link).toContainText('Ubah Password');
  });

  test('should highlight active menu item when navigated', async ({ page }) => {
    await page.click('a.nav-link[href*="mahasiswa"]');
    await expect(page).toHaveURL(/.*mahasiswa/);
    const activeItem = page.locator('.sidebar-menu li.active a[href*="mahasiswa"]');
    await expect(activeItem).toBeVisible();
  });
});

test.describe('Admin Dashboard Content', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
  });

  test('should show active gelombang highlight', async ({ page }) => {
    const gelombangCard = page.locator('.card.shadow-sm.border-0 .card-body');
    await expect(gelombangCard).toBeVisible();
    await expect(gelombangCard).toContainText('Gelombang Aktif Saat Ini');
  });

  test('should show Total Mahasiswa stat card', async ({ page }) => {
    const card = page.locator('.card-statistic-1').filter({ hasText: 'Total Mahasiswa' });
    await expect(card).toBeVisible();
    await expect(card.locator('.card-icon.bg-primary i.fas.fa-users')).toBeVisible();
  });

  test('should show Email Terverifikasi stat card', async ({ page }) => {
    const card = page.locator('.card-statistic-1').filter({ hasText: 'Email Terverifikasi' });
    await expect(card).toBeVisible();
    await expect(card.locator('.card-icon.bg-success i.fas.fa-user-check')).toBeVisible();
  });

  test('should show Biodata Belum Lengkap stat card', async ({ page }) => {
    const card = page.locator('.card-statistic-1').filter({ hasText: 'Biodata Belum Lengkap' });
    await expect(card).toBeVisible();
    await expect(card.locator('.card-icon.bg-warning i.fas.fa-file-alt')).toBeVisible();
  });

  test('should show chart for pendaftaran per gelombang', async ({ page }) => {
    await expect(page.locator('canvas#gelombangChart')).toBeVisible();
  });

  test('should show Mahasiswa Terbaru section', async ({ page }) => {
    await expect(page.locator('text=Mahasiswa Terbaru')).toBeVisible();
  });
});

test.describe('User Logout Flow', () => {
  test('should logout successfully via form submission', async ({ page }) => {
    await loginAsAdmin(page);
    const currentUrl = page.url();
    expect(currentUrl).toContain('/home');
    await page.locator('#logout-form').evaluate((form: HTMLFormElement) => form.requestSubmit());
    await page.waitForURL((url) => !url.toString().includes('/home'), { timeout: 10000 });
    expect(page.url()).not.toContain('/home');
  });
});
