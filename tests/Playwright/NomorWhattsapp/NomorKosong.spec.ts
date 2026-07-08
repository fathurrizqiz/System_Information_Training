import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Whattsapp Settings' }).click();
  await page.getByRole('link', { name: 'Daftar Nomor HP Kelola kontak' }).click();
  await page.getByRole('button', { name: 'Daftarkan Nomor' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama...' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama...' }).fill('mina');
  await page.getByText('MINAR NAPITUPULU, DR.').click();
  await page.getByRole('textbox', { name: '628123xxx' }).click();
  await page.getByRole('textbox', { name: '628123xxx' }).fill('');
  await page.getByRole('button', { name: 'Daftarkan Nomor' }).click();
  await expect(page.locator('text=Gagal menambahkan nomor. Pastikan data valid.')).toBeVisible();
});