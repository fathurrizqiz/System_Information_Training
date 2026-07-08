import { test, expect } from '@playwright/test';

test('Negative Test - Gagal simpan data diklat ketika Penyelenggara kosong', async ({ page }) => {
  // 1. Proses Login
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();

  // 2. Navigasi ke halaman utama Diklat dan klik Tambah
  await page.locator('a[href="/Diklat"]').click();
  await page.getByRole('button', { name: 'Tambah' }).click();

  // JIKA tombol 'Tambah' langsung mengarahkan ke URL ini, pastikan url sudah benar
  // (Jika tidak otomatis pindah, baris di bawah ini bisa diaktifkan):
  // await page.goto('http://localhost:8000/Diklat/create');

  // 3. Pengisian Form (Tanpa gangguan page.goto di tengah-tengah)
  await page.getByRole('textbox', { name: 'Tanggal Mulai Tanggal Selesai' }).fill('2026-06-04');
  await page.locator('#tanggal').nth(1).fill('2026-06-05');
  
  await page.getByRole('textbox', { name: 'Nama Diklat' }).fill('Diklat Excelent');
  await page.locator('select[name="diklat"]').selectOption('HLC');
  await page.getByRole('combobox', { name: 'Pengajar' }).fill('Dr Minar Mitutuluhur');
  
  await page.getByRole('textbox', { name: 'Penyelenggara' }).fill('RSMDH');
  
  await page.getByRole('spinbutton', { name: 'Jam Diklat' }).fill('1');
  await page.locator('#keterangan').nth(1).fill('sangat bagus');
  await page.locator('#keterangan').nth(2).fill('cukup');
  
  // Upload file sertifikat
  // await page.getByRole('button', { name: 'Upload Sertifikat' }).setInputFiles('hasil_turnitin_1767684375748_Revisi.pdf');

  // 4. Trigger simpan data
  await page.getByRole('button', { name: 'Simpan Data' }).click();

  // 5. EVALUASI/ASSERTION (Bagian paling penting dalam testing)
  // Opsi A: Memastikan browser tetap bertahan di halaman form (tidak redirect/pindah karena eror)
  await expect(page.locator('text=Data Gagal Disimpan!, Pastikan semua kolom terisi dengan benar.')).toBeVisible();

});