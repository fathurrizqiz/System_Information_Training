import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Rencana Diklat' }).click();
  await page.getByRole('link', { name: 'HLC' }).click();
  await page.getByRole('heading', { name: 'Diklat Skin Care' }).click();
  await page.getByRole('button', { name: 'Tambah Detail' }).nth(4).click();
  await page.getByRole('button', { name: 'Choose File' }).click();
  await page.getByRole('button', { name: 'Choose File' }).setInputFiles('hasil_turnitin_1767684375748_Revisi.pdf');
  await page.getByRole('textbox').nth(1).click();
  await page.getByRole('textbox').nth(1).fill('2');
  await page.getByRole('textbox', { name: 'Ketik nama atau NRP karyawan' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama atau NRP karyawan' }).fill('eva');
  await page.getByRole('main').getByText('EVA EFFENDI', { exact: true }).click();
  await page.getByRole('button', { name: 'Simpan Diklat' }).click();
  await expect(page.locator('text=Gagal menambahkan detail: The tanggal mulai field is required., The tanggal selesai field is required.')).toBeVisible();
});