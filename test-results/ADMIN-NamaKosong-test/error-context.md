# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: ADMIN\NamaKosong.spec.ts >> test
- Location: tests\Playwright\ADMIN\NamaKosong.spec.ts:3:1

# Error details

```
Error: page.goto: net::ERR_CONNECTION_REFUSED at http://localhost:8000/login
Call log:
  - navigating to "http://localhost:8000/login", waiting until "load"

```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | test('test', async ({ page }) => {
> 4  |   await page.goto('http://localhost:8000/login');
     |              ^ Error: page.goto: net::ERR_CONNECTION_REFUSED at http://localhost:8000/login
  5  |   await page.getByRole('textbox', { name: 'Enter your Employee ID' }).click();
  6  |   await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill(' NO \t Langkah Pengujian\t Data Input\t Hasil\t Status 1\tNama Karyawan\tkosong\tMenampilkan Notifikasi Error\t(PASS) 2\tNama Karyawan\tInput\tMenampilkan Notifikasi Success\t(PASS) 3\tNomor Wa\tkosong\tMenampilkan Notifikasi Error\t(PASS) 4\tNomor Wa\tinput\tMenampilkan Notifikasi Error\t(PASS)');
  7  |   await page.getByRole('textbox', { name: 'Enter your Employee ID' }).press('ControlOrMeta+z');
  8  |   await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005180106');
  9  |   await page.getByRole('textbox', { name: 'Enter Password' }).click();
  10 |   await page.getByRole('textbox', { name: 'Enter Password' }).fill('005180106');
  11 |   await page.getByRole('button', { name: 'Sign in →' }).click();
  12 |   await page.getByRole('link', { name: 'User Management' }).click();
  13 |   await page.getByRole('link', { name: 'Kelola Pengguna Atur pengguna' }).click();
  14 |   await page.getByRole('button', { name: 'Tambah User Baru' }).click();
  15 |   await page.getByRole('textbox').nth(1).click();
  16 |   await page.getByRole('textbox').nth(1).fill('005100444');
  17 |   await page.getByRole('textbox').nth(2).click();
  18 |   await page.getByRole('textbox').nth(2).fill('005100444');
  19 |   await page.getByRole('textbox').nth(3).click();
  20 |   await page.getByRole('textbox').nth(3).fill('005100444');
  21 |   await page.getByRole('button', { name: 'Buat User' }).click();
  22 |   await expect(page.locator('text=Gagal membuat user. Pastikan semua field diisi dengan benar.')).toBeVisible();
  23 | 
  24 | });
```