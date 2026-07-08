import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Whattsapp Settings' }).click();
  await page.getByRole('link', { name: 'Template Pesan Atur kata-kata' }).click();
  await page.getByRole('textbox', { name: 'Misal: Notifikasi Jadwal Baru' }).click();
  await page.getByRole('textbox', { name: 'Misal: Notifikasi Jadwal Baru' }).fill('Non-Klinis');
  await page.getByRole('textbox', { name: 'Halo {nama}, jadwal diklat {' }).click();
  await page.getByRole('textbox', { name: 'Halo {nama}, jadwal diklat {' }).fill('Halo {nama} ada pelatihan {judul} pada {tanggal} di {Lokasi}');
  await page.getByRole('button', { name: 'Simpan Template' }).click();
    await expect(page.locator('text=Template berhasil ditambahkan')).toBeVisible();
});