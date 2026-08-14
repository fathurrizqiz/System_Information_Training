<?php

namespace App\Http\Controllers\report;

use App\Exports\LaporanDiklatExport;
use App\Exports\RekapProgramExport;
use App\Http\Controllers\Controller;
use App\Models\MasterDataModels;
use App\Models\ProgramEksternal;
use App\Models\ProgramHlc;
use App\Models\TargetJamModels;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class GenerateReportController extends Controller
{
    public function generateReport(Request $request)
    {
        // 1. Inisialisasi Log & Identitas User
        $user = Auth::user();
        $namaUser = $user ? ($user->nama_karyawan ?? $user->name) : 'Guest';
        $ip = $request->ip();

        // Log::info("EXCEL_EXPORT_START: User [$namaUser] memulai proses generate laporan.");

        try {
            // 2. Validasi & Mapping Bulan
            $selectedMonths = $request->input('months', [now()->month]);
            if (!is_array($selectedMonths)) {
                $selectedMonths = [$selectedMonths];
            }

            $year = now()->year;
            $bulanIndo = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

            $namaBulanTerpilih = collect($selectedMonths)
                ->map(fn($m) => $bulanIndo[$m] ?? $m)
                ->implode(', ');

            // 3. Query Data dengan Relasi
            $data = MasterDataModels::with([
                'diklatByNrp',
                'diklatHlc',
                'diklatEksternal',
                'diklatInternalUtama.periode.aksi',
                'diklatInternalUtama.periode.detail'
            ])
                ->orderBy('nama_karyawan')
                ->get()
                ->map(function ($karyawan) use ($selectedMonths, $year) {

                    // Data diklat dari berbagai sumber
                    $internalMandiri = $karyawan->diklatByNrp->where('status', 'approved')->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'Diklat (Mandiri)',
                        'tempat' => $d->penyelenggara,
                        'pengajar' => $d->pengajar,
                    ]);

                    $hlc = $karyawan->diklatHlc->where('status', 'approved')->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'HLC',
                        'tempat' => $d->penyelenggara,
                    ]);

                    $eksternal = $karyawan->diklatEksternal->where('status', 'approved')->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'Eksternal',
                        'tempat' => $d->penyelenggara,
                    ]);

                    $internalUtama = $karyawan->diklatInternalUtama
                        ->whereNotNull('post_done_at')
                        ->map(function ($d) {
                            return [
                                'nama_diklat' => $d->periode->detail->nama_diklat ?? 'Diklat Internal',
                                'tanggal' => $d->periode->tanggal ?? null,
                                'jam' => (int) ($d->periode->aksi->first()->jam_diklat ?? 0),
                                'jenis' => 'Internal',
                                'pengajar' => $d->periode->nama_pengajar ?? '-',
                                'tempat' => $d->periode->tempat ?? '-',
                            ];
                        });

                    // Proses Penggabungan & Filter per Karyawan
                    $karyawan->rekam_jejak = collect()
                        ->concat($internalMandiri)
                        ->concat($hlc)
                        ->concat($eksternal)
                        ->concat($internalUtama)
                        ->filter(function ($item) use ($selectedMonths, $year) {
                        if (empty($item['tanggal']))
                            return false;
                        $date = Carbon::parse($item['tanggal']);
                        return in_array($date->month, $selectedMonths) && $date->year == $year;
                    })
                        ->sortByDesc('tanggal')
                        ->values();

                    $karyawan->total_jam_diklat = $karyawan->rekam_jejak->sum('jam');
                    return $karyawan;
                })
                // Filter hanya karyawan yang punya data di periode tsb
                ->filter(fn($k) => $k->rekam_jejak->count() > 0);

            // 4. Log Statistik Data
            $count = $data->count();
            // Log::info("EXCEL_EXPORT_PROCESSING: Periode [$namaBulanTerpilih]. Ditemukan $count karyawan.");

            if ($count === 0) {
                Log::warning("EXCEL_EXPORT_EMPTY: Laporan kosong untuk user $namaUser pada periode $namaBulanTerpilih.");
            }

            // 5. Eksekusi Download
            $fileName = "Laporan_Diklat_" . str_replace(', ', '_', $namaBulanTerpilih) . ".xlsx";

            // Log::info("EXCEL_EXPORT_SUCCESS: File [$fileName] berhasil dikirim ke user.");

            return Excel::download(
                new LaporanDiklatExport($data, $namaBulanTerpilih),
                $fileName
            );

        } catch (\Exception $e) {
            // 6. Log jika terjadi error (Penting!)
            // Log::error("EXCEL_EXPORT_FAILED: Terjadi kesalahan teknis. Pesan: " . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat memproses laporan.');
        }
    }

    public function generateUserReport(Request $request)
    {
        // 1. Inisialisasi Identitas User (NRP)
        $user = Auth::user();
        $namaUser = $user ? ($user->nama_karyawan ?? $user->name) : 'Guest';
        $nrpUser = $user->nrp;

        try {
            // 2. Validasi & Mapping Bulan
            $selectedMonths = $request->input('months', [now()->month]);
            if (!is_array($selectedMonths)) {
                $selectedMonths = [$selectedMonths];
            }

            $year = now()->year;
            $bulanIndo = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

            $namaBulanTerpilih = collect($selectedMonths)
                ->map(fn($m) => $bulanIndo[$m] ?? $m)
                ->implode(', ');

            // 3. Query Data HANYA UNTUK USER YANG LOGIN
            $data = MasterDataModels::with([
                'diklatByNrp',
                'diklatHlc',
                'diklatEksternal',
                'diklatInternalUtama.periode.aksi',
                'diklatInternalUtama.periode.detail'
            ])
                ->where('nrp', $nrpUser) // <--- INI KUNCI FILTERNYA
                ->get()
                ->map(function ($karyawan) use ($selectedMonths, $year) {

                    // Data diklat dari berbagai sumber
                    $internalMandiri = $karyawan->diklatByNrp->where('status', 'approved')->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'Diklat (Mandiri)',
                        'tempat' => $d->penyelenggara,
                        'pengajar' => $d->pengajar,
                    ]);

                    $hlc = $karyawan->diklatHlc->where('status', 'approved')->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'HLC',
                        'tempat' => $d->penyelenggara,
                    ]);

                    $eksternal = $karyawan->diklatEksternal->where('status', 'approved')->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'Eksternal',
                        'tempat' => $d->penyelenggara,
                    ]);

                    $internalUtama = $karyawan->diklatInternalUtama
                        ->whereNotNull('post_done_at')
                        ->map(function ($d) {
                            return [
                                'nama_diklat' => $d->periode->detail->nama_diklat ?? 'Diklat Internal',
                                'tanggal' => $d->periode->tanggal ?? null,
                                'jam' => (int) ($d->periode->aksi->first()->jam_diklat ?? 0),
                                'jenis' => 'Internal',
                                'pengajar' => $d->periode->nama_pengajar ?? '-',
                                'tempat' => $d->periode->tempat ?? '-',
                            ];
                        });

                    // Proses Penggabungan & Filter per Karyawan
                    $karyawan->rekam_jejak = collect()
                        ->concat($internalMandiri)
                        ->concat($hlc)
                        ->concat($eksternal)
                        ->concat($internalUtama)
                        ->filter(function ($item) use ($selectedMonths, $year) {
                        if (empty($item['tanggal']))
                            return false;
                        $date = Carbon::parse($item['tanggal']);
                        return in_array($date->month, $selectedMonths) && $date->year == $year;
                    })
                        ->sortByDesc('tanggal')
                        ->values();

                    $karyawan->total_jam_diklat = $karyawan->rekam_jejak->sum('jam');
                    return $karyawan;
                })
                // Filter agar koleksi kosong jika karyawan tidak punya data di bulan tsb
                ->filter(fn($k) => $k->rekam_jejak->count() > 0);

            // 4. Eksekusi Download
            // Modifikasi nama file agar mencantumkan nama user
            $namaFileSanitized = preg_replace('/[^A-Za-z0-9\-]/', '_', $namaUser);
            $fileName = "Riwayat_Diklat_{$namaFileSanitized}_" . str_replace(', ', '_', $namaBulanTerpilih) . ".xlsx";

            // Tetap menggunakan class Export yang sama karena strukturnya identik
            return Excel::download(
                new LaporanDiklatExport($data, $namaBulanTerpilih),
                $fileName
            );

        } catch (\Exception $e) {
            // Log::error("EXCEL_EXPORT_USER_FAILED: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memproses laporan Anda.');
        }
    }

    public function generateReportProgram(Request $request)
    {
        // Tangkap ID dan jenis dari request (misal dari URL: /export?jenis=eksternal&id=5)
        $programId = $request->input('id');
        $jenis = $request->input('jenis');

        try {
            // Inisialisasi collection kosong
            $dataEksternal = collect();
            $dataHlc = collect();
            $namaProgram = 'Semua_Program'; // Default fallback

            // Jika tidak ada ID, kembalikan error (opsional, untuk mencegah download semua data)
            if (!$programId || !$jenis) {
                return back()->with('error', 'Pilih program spesifik yang ingin diunduh.');
            }

            if ($jenis === 'eksternal') {

                // Ambil SATU program eksternal berdasarkan ID
                $program = ProgramEksternal::with([
                    'eksternal' => function ($query) {
                        $query->where('status', 'approved');
                    },
                    'eksternal.karyawan'
                ])->find($programId);

                if ($program) {
                    $dataEksternal->push($program);
                    // Bersihkan nama program dari karakter aneh untuk nama file
                    $namaProgram = preg_replace('/[^A-Za-z0-9\-]/', '_', $program->nama_diklat);
                }

            } elseif ($jenis === 'hlc') {

                // Ambil SATU program HLC berdasarkan ID
                $program = ProgramHlc::with([
                    'hlc' => function ($query) {
                        $query->where('status', 'approved');
                    },
                    'hlc.karyawan'
                ])->find($programId);

                if ($program) {
                    $dataHlc->push($program);
                    // Bersihkan nama program untuk nama file
                    $namaProgram = preg_replace('/[^A-Za-z0-9\-]/', '_', $program->nama_program);
                }
            }

            // Jika data tidak ditemukan
            if ($dataEksternal->isEmpty() && $dataHlc->isEmpty()) {
                return back()->with('error', 'Data program tidak ditemukan.');
            }

            // Nama file menjadi dinamis, misal: Laporan_Pelatihan_K3_20260814.xlsx
            $fileName = "Laporan_{$namaProgram}_" . now()->format('Ymd') . ".xlsx";

            // Eksekusi Download (Tetap menggunakan class Export yang sama)
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\RekapProgramExport($dataEksternal, $dataHlc),
                $fileName
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("EXCEL_GENERATE_ERROR: " . $e->getMessage());
            return back()->with('error', 'Gagal membuat laporan Excel.');
        }
    }
}
