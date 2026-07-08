import { test, expect } from '@playwright/test';

test('Test Tambah File - Menunggu Dialog Muncul', async ({ page }) => {
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).click();
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();
  await page.getByRole('link', { name: 'Library Materi' }).click();
  await page.getByText('Diklat Excelent').click();
  await page.getByRole('button', { name: 'Tambah Materi' }).click();
  await page.getByRole('combobox').selectOption('file');
  await page.getByRole('textbox', { name: 'Panduan SOP' }).click();
  await page.getByRole('textbox', { name: 'Panduan SOP' }).fill('Pelatihan Excelent Juni');
  await page.getByRole('button', { name: 'Choose File' }).click();
  await page.getByRole('button', { name: 'Choose File' }).setInputFiles('hasil_turnitin_1767684375748_Revisi.pdf');
  await page.getByRole('button', { name: 'Simpan Materi' }).click();

  await page.getByRole('button', { name: 'Verifikasi' }).click();

  
});