import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill(' NO \t Langkah Pengujian\t Data Input\t Hasil\t Status 1\tNama Karyawan\tkosong\tMenampilkan Notifikasi Error\t(PASS) 2\tNama Karyawan\tInput\tMenampilkan Notifikasi Success\t(PASS) 3\tNomor Wa\tkosong\tMenampilkan Notifikasi Error\t(PASS) 4\tNomor Wa\tinput\tMenampilkan Notifikasi Error\t(PASS)');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).press('ControlOrMeta+z');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005180106');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005180106');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'User Management' }).click();
  await page.getByRole('link', { name: 'Kelola Pengguna Atur pengguna' }).click();
  await page.goto('http://localhost:8000/super-admin/users');
  await page.getByRole('button', { name: 'Tambah User Baru' }).click();
  await page.getByRole('textbox').first().click();
  await page.getByRole('textbox').first().fill('Fathur');
  await page.getByRole('textbox').nth(1).click();
  await page.getByRole('textbox').nth(1).fill('005100444');
  await page.getByRole('button', { name: 'Buat User' }).click();
  await expect(page.locator('text=Gagal membuat user. Pastikan semua field diisi dengan benar.')).toBeVisible();
});