import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();

  // FIX: Menggunakan locator yang lebih bersih dan fleksibel
  // await page.getByRole('link', { name: 'Diklat', exact: false }).click(); 
  // ATAU alternatif lain yang sangat aman jika teksnya ambigu:
  await page.locator('a[href="/Diklat"]').click();

  await page.getByRole('button', { name: 'Tambah' }).click();
  await page.getByRole('textbox', { name: 'Nama Diklat' }).click();
  await page.getByRole('textbox', { name: 'Nama Diklat' }).fill('Diklat Excelent');
  await page.locator('select[name="diklat"]').selectOption('HLC');
  await page.getByRole('combobox', { name: 'Pengajar' }).click();
  await page.getByRole('combobox', { name: 'Pengajar' }).fill('dr Minar Mitutuluhur');
  await page.getByRole('textbox', { name: 'Penyelenggara' }).click();
  await page.getByRole('textbox', { name: 'Penyelenggara' }).fill('RSMDH');
  await page.getByRole('spinbutton', { name: 'Jam Diklat' }).click();
  await page.getByRole('spinbutton', { name: 'Jam Diklat' }).fill('-1');
  await page.locator('#keterangan').nth(1).click();
  await page.locator('#keterangan').nth(1).fill('Sangat bagus');
  await page.locator('#keterangan').nth(2).click();
  await page.locator('#keterangan').nth(2).fill('luar biasa');
  await page.locator('div').filter({ hasText: /^Jam Diklat$/ }).click();
  await page.getByRole('spinbutton', { name: 'Jam Diklat' }).fill('1');
  await page.getByRole('button', { name: 'Upload Sertifikat' }).click();
  await page.getByRole('button', { name: 'Upload Sertifikat' }).setInputFiles('hasil_turnitin_1767684375748_Revisi.pdf');
  await page.goto('http://localhost:8000/Diklat/create');
  await page.getByRole('button', { name: 'Simpan Data' }).click();
  await expect(page.locator('text=Data Gagal Disimpan!, Pastikan semua kolom terisi dengan benar.')).toBeVisible();
});