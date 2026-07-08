import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Jadwal Diklat' }).click();
  await page.getByRole('button', { name: 'eksternal' }).click();
  await page.getByRole('button', { name: 'Absen Hari Ini' }).click();
  await expect(page.locator('text=Absen berhasil!')).toBeVisible();
});