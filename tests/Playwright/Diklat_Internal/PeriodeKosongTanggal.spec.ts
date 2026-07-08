import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Rencana Diklat' }).click();
  await page.getByRole('heading', { name: 'Non-Klinis' }).click();
  await page.goto('http://localhost:8000/RencanaDiklat/RPT/PF');
  await page.getByRole('cell', { name: 'Diklat Excelent' }).click();
  await page.getByRole('textbox', { name: 'Cari nama karyawan...' }).click();
  await page.getByRole('textbox', { name: 'Cari nama karyawan...' }).fill('Min');
  await page.locator('div').filter({ hasText: /^MINAR NAPITUPULU, DR\.$/ }).click();
  await page.getByRole('textbox').nth(2).click();
  await page.getByRole('textbox').nth(2).fill('RSMDH');
  await page.getByRole('button', { name: 'Tambah Periode' }).click();
  await expect(page.locator('text=Lengkapi Periode!')).toBeVisible();
});