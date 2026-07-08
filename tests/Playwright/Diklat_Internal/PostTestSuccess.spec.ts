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
  await page.locator('div:nth-child(4) > .overflow-x-auto > .w-full > .divide-y > .cursor-pointer > .flex.gap-3 > .flex.w-20.justify-center.gap-2.rounded-xl.bg-gradient-to-r.from-blue-600').click();
  await page.getByRole('combobox').selectOption('50');
//   await page.goto('http://localhost:8000/RencanaDiklat/Internal/detail/aksi/48');
  await page.getByRole('button', { name: 'Post-test' }).click();
  await page.getByRole('button', { name: '+ Tambah Soal' }).click();
  await page.getByRole('textbox', { name: 'Tulis soal...' }).click();
  await page.getByRole('textbox', { name: 'Tulis soal...' }).fill('Apa yang perlu karyawan lakukan ketika mendapat sebuah panggilan darurat');
  await page.getByRole('textbox', { name: 'Teks jawaban...' }).click();
  await page.getByRole('textbox', { name: 'Teks jawaban...' }).fill('Segera mencatat ');
  await page.getByRole('button').nth(3).click();
  await page.getByRole('button', { name: '+ Tambah Pilihan' }).click();
  await page.getByRole('textbox', { name: 'Teks jawaban...' }).nth(1).click();
  await page.getByRole('textbox', { name: 'Teks jawaban...' }).nth(1).fill('cukup dengan mendengarkan');
  await page.getByRole('button', { name: 'Simpan Post-Test' }).click({timeout: 10000});
  await expect(page.locator('text=Post-Test Berhasil disimpan')).toBeVisible();
});