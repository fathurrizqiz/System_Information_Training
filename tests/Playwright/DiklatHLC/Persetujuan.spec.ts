import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Persetujuan' }).click();
  await page.getByRole('button', { name: 'Diklat HLC Persetujuan' }).click();
  await page.getByText('Hadir').first().click();
  await page.getByRole('button', { name: 'Detail & Verifikasi' }).nth(4).click();
  await page.getByText('Proses VerifikasiStatus').click();
  await page.getByRole('combobox').selectOption('Disetujui');
  await page.getByRole('button', { name: 'Simpan Verifikasi' }).click({ timeout: 10000 });
await expect(
  page.getByText('Verifikasi berhasil disimpan')
).toBeVisible({ timeout: 5000 });
  
});