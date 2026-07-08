import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Rencana Diklat' }).click();
  const eksternalLink = page.locator('a[href="/RencanaDiklat/RPT/PN"]');

    await expect(eksternalLink).toBeVisible({
        timeout: 10000,
    });

    await eksternalLink.click();

    await expect(page).toHaveURL(/RencanaDiklat\/RPT\/PN/);

    // tunggu halaman Eksternal siap
    await expect(
        page.getByRole('button', { name: 'Tambah Program' }),
    ).toBeVisible({
        timeout: 10000,
    });
  await page.getByRole('heading', { name: 'CSSU' }).click();
  await page.getByRole('button', { name: 'Tambah Peserta' }).nth(4).click();
  await page.getByRole('button', { name: 'Simpan' }).click();
  await expect(
    page.getByText('Jam diklat optimalnya antara 1 dan 9 jam'),
  ).toBeVisible();
});