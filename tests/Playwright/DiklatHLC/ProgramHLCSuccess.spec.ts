import { test, expect } from '@playwright/test';

test('Tambah Program HLC', async ({ page }) => {
  await page.goto('http://localhost:8000/login');

  await page
    .getByRole('textbox', { name: 'Enter your Employee ID' })
    .fill('005100439');

  await page
    .getByRole('textbox', { name: 'Enter Password' })
    .fill('005100439');

  await page.getByRole('button', { name: 'Sign in →' }).click();

  // await expect(page).toHaveURL(/dashboard/);

  await page.getByRole('link', { name: 'Rencana Diklat' }).click();

  const hlcLink = page.locator('a[href="/HLC/Home/manajemen"]');
  await expect(hlcLink).toBeVisible();
  await hlcLink.click();

  await expect(page).toHaveURL(/HLC\/Home\/manajemen/);

  await page.getByRole('button', { name: 'Tambah Program' }).click();

  const namaProgram = page.getByRole('textbox').nth(1);

  await expect(namaProgram).toBeVisible();
  await namaProgram.fill('Diklat Skin Care');

  await page.getByRole('button', { name: 'Simpan' }).click();
  await expect(page.locator('text=Berhasil ditambah')).toBeVisible();
});