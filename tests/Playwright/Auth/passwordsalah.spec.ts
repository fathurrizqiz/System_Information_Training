import { test, expect } from '@playwright/test';

test('Test Login Password Salah - Menunggu Toast Gagal Muncul', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('123456789');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await expect(page.locator('text=Login gagal. Pastikan NRP dan Password benar.')).toBeVisible();
});