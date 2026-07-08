import { test, expect } from '@playwright/test';

test('Pree-test Soal Kosong', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Rencana Diklat' }).click();
  await page.goto('http://localhost:8000/RencanaDiklat/RPT/PF');
  await page.getByRole('heading', { name: 'Non-Klinis' }).click();
  await page.locator('div:nth-child(4) > .overflow-x-auto > .w-full > .divide-y > .cursor-pointer > .flex.gap-3 > .flex.w-20.justify-center.gap-2.rounded-xl.bg-gradient-to-r.from-blue-600').click();
  await page.getByRole('combobox').selectOption('50');
  // await page.goto('http://localhost:8000/RencanaDiklat/Internal/detail/aksi/44');
  await page.getByRole('button', { name: 'Pre-test' }).click();
  await page.getByRole('button', { name: '+ Tambah Soal' }).click();
  await page.getByRole('button', { name: 'Simpan Pre-Test' }).click();
  await expect(page.locator('text=Pastikan Data Terisi dengan Benar!:')).toBeVisible();
});