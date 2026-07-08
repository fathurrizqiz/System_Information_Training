<?php

namespace App\Http\Controllers\report;

use App\Exports\LaporanDiklatExport;
use App\Http\Controllers\Controller;
use App\Models\MasterDataModels;
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
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
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
                    'tempat' =>$d->penyelenggara,
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
                        if (empty($item['tanggal'])) return false;
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
}
