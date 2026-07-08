import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005180106');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005180106');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'User Management' }).click();
  await page.getByRole('link', { name: 'Kelola Pengguna Atur pengguna' }).click();
  await page.getByRole('button', { name: 'Tambah User Baru' }).click();
  await page.getByRole('textbox').nth(1).click();
  await page.getByRole('textbox').nth(1).fill('FATHUR RIZQI');
  await page.getByRole('textbox').nth(2).click();
  await page.getByRole('textbox').nth(2).fill('123456789');
  await page.getByRole('textbox').nth(3).click();
  await page.getByRole('textbox').nth(3).fill('123456789');
  await page.getByRole('textbox').nth(4).click();
  await page.getByRole('textbox').nth(4).fill('123456789');
  await page.getByRole('button', { name: 'Buat User' }).click();
  await expect(page.getByText('User berhasil dibuat')).toBeVisible();
});