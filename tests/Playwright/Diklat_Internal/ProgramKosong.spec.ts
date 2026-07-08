import { test, expect } from '@playwright/test';

test('Program Kosong', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Rencana Diklat' }).click();
  await page.getByRole('button', { name: 'Tambah Program' }).click();
  await page.getByRole('button', { name: 'Simpan' }).click();
  await expect(page.locator('text=Nama program dan kategori harus diisi!')).toBeVisible();
});