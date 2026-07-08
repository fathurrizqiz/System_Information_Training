import { test } from '@playwright/test';

test('Test Tambah File Kosong - Menunggu Dialog Muncul', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Library Materi' }).click();
  await page.getByRole('button', { name: 'Tambah Materi' }).click();
  await page.getByRole('combobox').selectOption('file');
  await page.getByRole('textbox', { name: 'Panduan SOP' }).click();
  page.once('dialog', dialog => {
    console.log(`Dialog message: ${dialog.message()}`);
    dialog.dismiss().catch(() => {});
  });
  await page.getByRole('button', { name: 'Simpan Materi' }).click();
});