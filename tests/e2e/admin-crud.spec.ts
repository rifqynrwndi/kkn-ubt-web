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

test.describe('Hak Akses (Role Management)', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/hakakses');
  });

  test('should display hak akses index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Role Access');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should show users with role badges', async ({ page }) => {
    const badges = page.locator('.badge');
    await expect(badges.first()).toBeVisible({ timeout: 10000 });
  });
});

test.describe('Data Mahasiswa Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/mahasiswa');
  });

  test('should display mahasiswa index page with table', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Data Mahasiswa');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should have create button', async ({ page }) => {
    const createBtn = page.locator('a[href*="mahasiswa/create"]');
    await expect(createBtn.first()).toBeVisible();
  });

  test('should navigate to create form', async ({ page }) => {
    const createBtn = page.locator('a[href*="mahasiswa/create"]').first();
    await createBtn.click();
    await expect(page).toHaveURL(/.*mahasiswa\/create/);
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });

  test('should have create form fields', async ({ page }) => {
    await page.goto('/mahasiswa/create');
    await expect(page.locator('input[name="name"], input#name')).toBeVisible();
    await expect(page.locator('input[name="email"], input#email')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });
});

test.describe('Gelombang KKN Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/gelombang');
  });

  test('should display gelombang index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Gelombang');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should show gelombang data from seeder', async ({ page }) => {
    const tableText = await page.locator('table').textContent();
    expect(tableText).toContain('KKN');
  });
});

test.describe('DPL Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/pembimbing-lapangan');
  });

  test('should display DPL index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Dosen Pembimbing Lapangan');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should show DPL records from seeder', async ({ page }) => {
    await expect(page.locator('table tbody tr').first()).toBeVisible();
  });
});

test.describe('Data Desa Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/desa');
  });

  test('should display desa index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Data Desa');
  });

  test('should show desa data', async ({ page }) => {
    await expect(page.locator('table tbody tr').first()).toBeVisible();
  });
});

test.describe('Kelompok KKN Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/kelompok-kkn');
  });

  test('should display kelompok index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Kelompok KKN');
    await expect(page.locator('table')).toBeVisible();
  });

  test('should have create button', async ({ page }) => {
    const createBtn = page.locator('a[href*="kelompok-kkn/create"], a:has-text("Tambah")');
    await expect(createBtn.first()).toBeVisible();
  });

  test('should show existing kelompok from seeder', async ({ page }) => {
    await expect(page.locator('table tbody tr').first()).toBeVisible();
  });
});

test.describe('Fakultas & Prodi Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/fakultas-prodi');
  });

  test('should display fakultas prodi index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Fakultas');
  });

  test('should show fakultas data from seeder', async ({ page }) => {
    const bodyText = await page.locator('body').textContent();
    expect(bodyText).toContain('Teknik');
  });
});

test.describe('Admin Tugas Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/tugas');
  });

  test('should display admin tugas index page', async ({ page }) => {
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
    await expect(page.locator('table').first()).toBeVisible();
  });

  test('should have create button', async ({ page }) => {
    const createBtn = page.locator('a[href*="admin/tugas/create"], a:has-text("Tambah")');
    await expect(createBtn.first()).toBeVisible();
  });

  test('should navigate to create page', async ({ page }) => {
    await page.click('a[href*="admin/tugas/create"], a:has-text("Tambah")');
    await expect(page).toHaveURL(/.*admin\/tugas\/create/);
  });
});

test.describe('Admin Tugas Create Page', () => {
  test('should display create form', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/tugas/create');
    await expect(page.locator('#sidebar-wrapper').first()).toBeVisible();
  });
});

test.describe('Verifikasi Dokumen', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/verifikasi-dokumen');
  });

  test('should display verifikasi dokumen index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Verifikasi');
  });
});

test.describe('Profile Management', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/profile/edit');
  });

  test('should display profile edit page', async ({ page }) => {
    await expect(page.locator('.main-wrapper')).toBeVisible();
  });

  test('should have name and email fields', async ({ page }) => {
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });
});

test.describe('Change Password Page', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/profile/change-password');
  });

  test('should display change password page', async ({ page }) => {
    await expect(page.locator('.main-wrapper')).toBeVisible();
  });

  test('should have password input fields', async ({ page }) => {
    const passwordInputs = page.locator('input[type="password"]');
    const count = await passwordInputs.count();
    expect(count).toBeGreaterThanOrEqual(2);
  });
});

test.describe('Admin WAR Pages', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/war');
  });

  test('should display WAR admin index page', async ({ page }) => {
    await expect(page.locator('.main-wrapper')).toBeVisible();
  });

  test('should have create WAR button', async ({ page }) => {
    const createBtn = page.locator('a[href*="admin/war/create"], a:has-text("Buat Sesi")');
    await expect(createBtn.first()).toBeVisible();
  });

  test('should navigate to create WAR page', async ({ page }) => {
    await page.click('a[href*="admin/war/create"], a:has-text("Buat Sesi")');
    await expect(page).toHaveURL(/.*admin\/war\/create/);
  });
});

test.describe('WAR Admin Create', () => {
  test('should display WAR create form', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/war/create');
    await expect(page.locator('.main-wrapper')).toBeVisible();
  });
});

test.describe('Admin Penilaian', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/admin/penilaian');
  });

  test('should display penilaian admin index page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Penilaian');
  });
});

test.describe('Notification Admin', () => {
  test.beforeEach(async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/notifications');
  });

  test('should display notifications page', async ({ page }) => {
    await expect(page.locator('.section-header h1')).toContainText('Notifikasi');
  });

  test('should have send notification button for admin', async ({ page }) => {
    const sendBtn = page.locator('a[href*="notifications/admin/create"], a:has-text("Kirim")');
    await expect(sendBtn.first()).toBeVisible();
  });
});

test.describe('Notification Send Form', () => {
  test('should display notification send page', async ({ page }) => {
    await loginAsAdmin(page);
    await page.goto('/notifications/admin/create');
    await expect(page.locator('.main-wrapper')).toBeVisible();
  });
});
