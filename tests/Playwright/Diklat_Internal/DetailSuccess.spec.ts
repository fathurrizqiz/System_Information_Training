import { expect, test } from '@playwright/test';

test('Detail Success', async ({ page }) => {
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
    await page.getByRole('heading', { name: 'Non-Klinis' }).click();
    await page.getByRole('button', { name: 'Tambah Detail' }).nth(3).click();
    await page.getByRole('textbox', { name: 'Nama Diklat' }).click();
    await page
        .getByRole('textbox', { name: 'Nama Diklat' })
        .fill('Diklat Excelent');
    await page.getByRole('textbox', { name: 'Keterangan' }).click();
    await page.getByRole('textbox', { name: 'Keterangan' }).fill('Non Medis');
    await page.getByRole('textbox', { name: 'Pengajar' }).click();
    await page.getByRole('textbox', { name: 'Pengajar' }).fill('Dr DEDE');
    await page.getByRole('button', { name: 'Simpan Detail' }).click();
    await expect(
        page.locator('text=Detail Diklat berhasil ditambahkan!'),
    ).toBeVisible();
});
