import { expect, test } from '@playwright/test';

test('test', async ({ page }) => {
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
    await page.goto('http://localhost:8000/RencanaDiklat/RPT/PF');
    await page.getByRole('heading', { name: 'Non-Klinis' }).click();
    await page
        .locator(
            'div:nth-child(4) > .overflow-x-auto > .w-full > .divide-y > .cursor-pointer > .flex.gap-3 > .flex.w-20.justify-center.gap-2.rounded-xl.bg-gradient-to-r.from-blue-600',
        )
        .click();
    await page.getByRole('combobox').selectOption('50');
 
    await page.getByRole('button', { name: 'Buat Template' }).click();
    await page
        .getByRole('textbox', { name: 'Nama materi / pembahasan' })
        .click();
    await page
        .getByRole('textbox', { name: 'Nama materi / pembahasan' })
        .fill('Konsep pelayanan prima');
    await page.getByRole('button', { name: '+ Tambah Materi' }).click();

    await page
        .getByRole('textbox', { name: 'Nama materi / pembahasan' })
        .nth(1)
        .click();
    await page
        .getByRole('textbox', { name: 'Nama materi / pembahasan' })
        .nth(1)
        .fill('Komunikasi efektif');

    await page.getByRole('button', { name: 'Simpan' }).click();
    await expect(page.locator('text=Materi Berhasil Disimpan')).toBeVisible();
});
