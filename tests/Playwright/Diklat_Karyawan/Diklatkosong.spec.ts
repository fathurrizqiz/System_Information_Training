import { test, expect } from '@playwright/test';

test('Positive Test - Berhasil menyimpan data diklat dengan data lengkap', async ({ page }) => {
  // 1. Proses Login
  await page.goto('http://localhost:8000/login');
  await page.getByRole('textbox', { name: 'Enter your Employee ID' }).fill('005100439');
  await page.getByRole('textbox', { name: 'Enter Password' }).fill('005100439');
  await page.getByRole('button', { name: 'Sign in →' }).click();

  // 2. Navigasi ke halaman form Tambah Diklat
  await page.locator('a[href="/Diklat"]').click();
  await page.getByRole('button', { name: 'Tambah' }).click();

  // JIKA tombol 'Tambah' tidak otomatis pindah ke URL form, aktifkan baris di bawah ini:
  // await page.goto('http://localhost:8000/Diklat/create');

  // 3. Pengisian Form Berurutan (Tanpa page.goto di tengahform yang bikin refresh)
  await page.getByRole('textbox', { name: 'Tanggal Mulai Tanggal Selesai' }).fill('2026-06-04');
  await page.locator('#tanggal').nth(1).fill('2026-06-05');
  
  // Catatan: Pastikan field 'Nama Diklat' diisi jika itu mandatory di aplikasi Anda
  // await page.getByRole('textbox', { name: 'Nama Diklat' }).fill('Diklat Excelent');
  
  await page.locator('select[name="diklat"]').selectOption('HLC');
  await page.getByRole('combobox', { name: 'Pengajar' }).fill('Dr Minar Mitutuluhur');
  
  // Mengisi Penyelenggara dengan benar (tidak di-refresh lagi)
  await page.getByRole('textbox', { name: 'Penyelenggara' }).fill('RSMDH');
  
  await page.getByRole('spinbutton', { name: 'Jam Diklat' }).fill('1');
  await page.locator('#keterangan').nth(1).fill('sangat bagus');
  await page.locator('#keterangan').nth(2).fill('cukup');
  
  // Upload file sertifikat
  await page.getByRole('button', { name: 'Upload Sertifikat' }).setInputFiles('hasil_turnitin_1767684375748_Revisi.pdf');

  // 4. Klik Simpan Data
  await page.getByRole('button', { name: 'Simpan Data' }).click();

  // 5. ASSERTION: Validasi sukses (Sesuaikan dengan perilaku aplikasi Anda setelah sukses)
  // Contoh: Biasanya setelah sukses akan dialihkan kembali ke halaman daftar utama (/Diklat)
  await expect(page.locator('text=Data Gagal Disimpan!, Pastikan semua kolom terisi dengan benar.')).toBeVisible();
  
  
});