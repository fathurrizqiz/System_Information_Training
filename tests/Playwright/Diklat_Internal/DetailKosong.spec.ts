import { expect, test } from '@playwright/test';

test('Detail Kosong', async ({ page }) => {
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
    await page.getByRole('button', { name: 'Tambah Detail' }).nth(3).click();
    await page.getByRole('button', { name: 'Simpan Detail' }).click();
    await expect(page.locator('text=Nama Diklat harus diisi!')).toBeVisible();
});
