import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/');
  await page.getByText('Klik di mana saja untuk').click();
  await page.locator('input[name="nrp"]').click();
  await page.locator('input[name="nrp"]').fill('005100439');
  await page.locator('input[name="password"]').click();
  await page.locator('input[name="password"]').fill('005100439');
  await page.getByRole('button', { name: 'Login' }).click();
  await page.waitForURL('**/dashboard');
  const menuWhattsapp = page.getByRole('link', {
        name: 'Whattsapp Settings',
    });
    // 2. Tambah Program Baru
    await menuWhattsapp.waitFor({ state: 'visible' });
    await menuWhattsapp.click();
  await page.getByRole('link', { name: 'Daftar Nomor HP Kelola kontak' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama...' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama...' }).fill('desi');
  await page.locator('div').filter({ hasText: /^DESI MUSTIKA$/ }).click();
  await page.getByRole('textbox', { name: '628123xxx' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama...' }).click();
  await page.getByRole('textbox', { name: 'Ketik nama...' }).fill('masru');
  await page.locator('div').filter({ hasText: /^MASRUKHA$/ }).nth(1).click();
  await page.getByRole('textbox', { name: '628123xxx' }).click();
  await page.getByRole('textbox', { name: '628123xxx' }).fill('6287793941055');
  await page.getByRole('button', { name: 'Daftarkan Nomor' }).click();
});