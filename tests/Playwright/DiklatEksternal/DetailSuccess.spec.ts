import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  // await page.getByRole('link', { name: 'Rencana Diklat' }).click();
  // await page.getByRole('link', { name: 'Eksternal' }).click();
  // await page.getByRole('button', { name: 'Tambah Peserta' }).nth(4).click();
  // await page.getByRole('textbox', { name: 'Ketik NRP/Nama...' }).click();
  // await page.getByRole('textbox', { name: 'Ketik NRP/Nama...' }).fill('eva');
  // await page.getByText('- EVA EFFENDI').click();
  // await page.getByRole('button', { name: 'Choose File' }).click();
  // await page.getByRole('button', { name: 'Choose File' }).setInputFiles('hasil_turnitin_1767684375748_Revisi.pdf');
  // await page.getByRole('textbox').nth(2).fill('2026-06-30');
  // await page.getByRole('textbox').nth(3).fill('2026-06-30');
  // await page.getByRole('textbox').nth(4).click();
  // await page.getByRole('textbox').nth(4).fill('1');
  // await page.getByRole('textbox').nth(4).fill('1');
  // await page.getByRole('button', { name: 'Simpan' }).click();
  await page.getByRole('link', { name: 'Inbox' }).click();
  await page.getByRole('button', { name: 'Tolak' }).click({ timeout: 10000 });
  await expect(page.locator('text=Konfirmasi Penolakan')).toBeVisible();
});