import { expect, test } from '@playwright/test';

test('Program Success', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
    await page
        .getByRole('textbox', { name: 'Enter your Employee ID' })
        .fill('005100439');
    await page.getByRole('textbox', { name: 'Enter Password' }).click();
    await page
        .getByRole('textbox', { name: 'Enter Password' })
        .fill('005100439');
    await page.getByRole('button', { name: 'Sign in →' }).click();
    await page.getByRole('link', { name: 'Rencana Diklat' }).click();
    await page.getByRole('button', { name: 'Tambah Program' }).click();
    await page
        .getByRole('textbox', { name: 'contoh: Diklat Kepemimpinan' })
        .click();
    await page
        .getByRole('textbox', { name: 'contoh: Diklat Kepemimpinan' })
        .fill('Non-Klinis');
    await page.getByRole('button', { name: 'Simpan' }).click();
    await expect(page.locator('text=Program Berhasil Ditambah!')).toBeVisible();
});
