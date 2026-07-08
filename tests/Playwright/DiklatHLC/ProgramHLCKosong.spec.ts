import { test, expect } from '@playwright/test';

test('Program HLC Kosong', async ({ page }) => {
  // Login
  await page.goto('http://localhost:8000/login');

  await page
    .getByRole('textbox', { name: 'Enter your Employee ID' })
    .fill('005100439');

  await page
    .getByRole('textbox', { name: 'Enter Password' })
    .fill('005100439');

  await page.getByRole('button', { name: 'Sign in →' }).click();



  // Klik menu Rencana Diklat
  await page.getByRole('link', { name: 'Rencana Diklat' }).click();

  // Tunggu navbar HLC muncul
  const hlcLink = page.locator('a[href="/HLC/Home/manajemen"]');

  await expect(hlcLink).toBeVisible({
    timeout: 10000,
  });

  await hlcLink.click();

  // Pastikan benar-benar masuk halaman HLC
  await expect(page).toHaveURL(/HLC\/Home\/manajemen/);

  // Verifikasi heading HLC tampil
  await expect(
    page.getByRole('heading', {
      name: 'Human Learning Center (HLC)',
    })
  ).toBeVisible();

  // Klik tambah program
  await page.getByRole('button', { name: 'Tambah Program' }).click();

  // DEBUG
  await page.pause();

  // Jika modal muncul
  const simpanButton = page.getByRole('button', {
    name: 'Simpan',
  });

  await expect(simpanButton).toBeVisible();

  await simpanButton.click();

  await expect(
    page.getByText('Nama program wajib diisi')
  ).toBeVisible();
});