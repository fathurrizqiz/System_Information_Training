<?php

namespace App\Http\Controllers\report;

use App\Exports\RekapDatabaseExport;
use App\Http\Controllers\Controller;
use App\Models\Karyawans;
use App\Models\RekapJamDiklat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Opsi Pilihan Tahun (Tahun Ini & 3 Tahun Ke Belakang)
        $currentYear = now()->year;
        $availableYears = range($currentYear, $currentYear - 3);

        // Capture Request Parameters
        $year = (int) $request->input('year', $currentYear);
        $selectedMonths = $request->input('months', [now()->month]);
        $searchbagian = $request->input('bagian', null);

        if (!is_array($selectedMonths)) {
            $selectedMonths = [(int) $selectedMonths];
        }

        $countSelectedMonths = count($selectedMonths);

        // 2. Ambil Aktual Jam per Bagian
        $aktualPerBagian = Karyawans::join('rekap_jam_diklat', 'karyawans.nrp', '=', 'rekap_jam_diklat.nrp')
            ->whereIn('rekap_jam_diklat.bulan', $selectedMonths)
            ->where('rekap_jam_diklat.tahun', $year)
            ->groupBy('karyawans.bagian')
            ->pluck(\DB::raw('SUM(rekap_jam_diklat.total_jam) as total_aktual'), 'bagian');

        // 3. Ambil List Karyawan Detail per Bagian
        $karyawanDetail = Karyawans::leftJoin('rekap_jam_diklat', function ($join) use ($selectedMonths, $year) {
            $join->on('karyawans.nrp', '=', 'rekap_jam_diklat.nrp')
                ->whereIn('rekap_jam_diklat.bulan', $selectedMonths)
                ->where('rekap_jam_diklat.tahun', $year);
        })
            ->leftJoin('target_jam_datamaster', 'karyawans.klinis_non_klinis', '=', 'target_jam_datamaster.kategori')
            ->select(
                'karyawans.bagian',
                'karyawans.unit_kerja',
                'karyawans.nrp',
                'karyawans.nama_karyawan',
                \DB::raw('SUM(COALESCE(rekap_jam_diklat.total_jam, 0)) as jam_aktual'),
                \DB::raw('MAX(target_jam_datamaster.target_jam) as target_dasar')
            )
            ->groupBy('karyawans.bagian', 'karyawans.unit_kerja', 'karyawans.nrp', 'karyawans.nama_karyawan')
            ->get()
            ->groupBy('bagian')
            ->map(fn ($karyawans) => $karyawans->groupBy(fn ($karyawan) => $karyawan->unit_kerja ?: 'Tanpa Unit Kerja'));

        // 4. Ambil Target per Bagian
        $queryTarget = Karyawans::leftJoin('target_jam_datamaster', 'karyawans.klinis_non_klinis', '=', 'target_jam_datamaster.kategori')
            ->select('karyawans.bagian')
            ->selectRaw('COUNT(karyawans.nrp) as total_karyawan')
            ->selectRaw('SUM(target_jam_datamaster.target_jam) as total_target_jam_dasar');

        if ($searchbagian) {
            $queryTarget->where('karyawans.bagian', 'ILIKE', '%' . $searchbagian . '%');
        }

        $targetKaryawanPerBagian = $queryTarget->groupBy('karyawans.bagian')->get();

        // 5. Gabungkan Data Final Dashboard
        $dataFinal = $targetKaryawanPerBagian->map(function ($row) use ($aktualPerBagian, $countSelectedMonths, $karyawanDetail) {
            $targetTotal = $row->total_target_jam_dasar * $countSelectedMonths;
            $aktual = $aktualPerBagian[$row->bagian] ?? 0;

            $listUnitKerja = collect($karyawanDetail->get($row->bagian, collect()))
                ->map(function ($karyawans, $unitKerja) use ($countSelectedMonths) {
                    return [
                        'unitKerja' => $unitKerja,
                        'karyawans' => $karyawans->map(function ($k) use ($countSelectedMonths) {
                            $t_individu = $k->target_dasar * $countSelectedMonths;

                            return [
                                'nrp' => $k->nrp,
                                'nama' => $k->nama_karyawan,
                                'aktual' => (float) $k->jam_aktual,
                                'target' => round($t_individu, 2),
                                'persentase' => $t_individu > 0 ? round(($k->jam_aktual / $t_individu) * 100, 2) : 0,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            return [
                'kategori' => $row->bagian,
                'totalKaryawan' => $row->total_karyawan,
                'totalTargetJam' => round($targetTotal, 2),
                'aktualJam' => (float) $aktual,
                'persentase' => $targetTotal > 0 ? round(($aktual / $targetTotal) * 100, 2) : 0,
                'unitKerjas' => $listUnitKerja,
            ];
        });

        $targetAll = $dataFinal->sum('totalTargetJam');
        $rekapJamTerpilih = RekapJamDiklat::whereIn('bulan', $selectedMonths)
            ->where('tahun', $year)
            ->sum('total_jam');

        // 6. Ambil Rekam Jejak Detail Per Karyawan (Difilter Berdasarkan $year yang dipilih)
        $data = Karyawans::with([
            'diklatByNrp',
            'diklatHlc',
            'diklatEksternal',
            'diklatInternalUtama.periode.aksi',
            'diklatInternalUtama.periode.detail'
        ])
            ->orderBy('nama_karyawan')
            ->get()
            ->map(function ($karyawan) use ($year) {

                $internalMandiri = $karyawan->diklatByNrp
                    ->where('status', 'approved')
                    ->filter(fn($d) => $d->tanggal_mulai && Carbon::parse($d->tanggal_mulai)->year === $year)
                    ->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal_mulai' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'Diklat (Mandiri)'
                    ]);

                $hlc = $karyawan->diklatHlc
                    ->where('status', 'approved')
                    ->filter(fn($d) => $d->tanggal_mulai && Carbon::parse($d->tanggal_mulai)->year === $year)
                    ->map(fn($d) => [
                        'nama_diklat' => $d->hlc->nama_program ?? '-',
                        'tanggal_mulai' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'HLC'
                    ]);

                $eksternal = $karyawan->diklatEksternal
                    ->where('status', 'approved')
                    ->filter(fn($d) => $d->tanggal_mulai && Carbon::parse($d->tanggal_mulai)->year === $year)
                    ->map(fn($d) => [
                        'nama_diklat' => $d->nama_diklat,
                        'tanggal_mulai' => $d->tanggal_mulai,
                        'jam' => $d->jam_diklat,
                        'jenis' => 'Eksternal'
                    ]);

                $internalUtama = $karyawan->diklatInternalUtama
                    ->whereNotNull('sertifikat_generated_at')
                    ->filter(fn($d) => isset($d->periode->tanggal) && Carbon::parse($d->periode->tanggal)->year === $year)
                    ->map(function ($d) {
                        return [
                            'nama_diklat' => $d->periode->detail->nama_diklat ?? 'Diklat Internal',
                            'tanggal' => $d->periode->tanggal ?? '-',
                            'jam' => (int) ($d->periode->aksi->first()->jam_diklat ?? 0),
                            'jenis' => 'Internal'
                        ];
                    });

                $karyawan->rekam_jejak = collect()
                    ->concat($internalMandiri)
                    ->concat($hlc)
                    ->concat($eksternal)
                    ->concat($internalUtama)
                    ->sortByDesc('tanggal_mulai')
                    ->values();

                $karyawan->total_jam_diklat = $karyawan->rekam_jejak->sum('jam');

                return $karyawan;
            });

        return Inertia::render('report/Report', [
            'filters' => [
                'months' => $selectedMonths,
                'year' => $year,
                'bagian' => $searchbagian,
            ],
            'availableYears' => $availableYears, 
            'totalPerKategori' => $dataFinal,
            'totalJamDiklat' => (float) $rekapJamTerpilih,
            'targetAll' => round($targetAll, 2),
            'rekam_jejak' => $data,
        ]);
    }

    public function exportLaporanDatabase(Request $request)
    {
        $searchbagian = $request->input('bagian', null);
        
        // Ambil data karyawan beserta relasi yang dibutuhkan
        $query = Karyawans::with([
            'diklatByNrp',
            'diklatHlc',
            'diklatEksternal',
            'diklatInternalUtama.periode.aksi',
            'diklatInternalUtama.periode.detail'
        ])->orderBy('bagian')->orderBy('unit_kerja')->orderBy('nama_karyawan');

        if ($searchbagian) {
            $query->where('bagian', 'ILIKE', '%' . $searchbagian . '%');
        }

        $karyawans = $query->get();

        // Siapkan Collection kosong untuk menampung baris Excel
        $excelRows = collect();

        foreach ($karyawans as $karyawan) {
            // 1. Data dari Diklat Mandiri
            $internalMandiri = $karyawan->diklatByNrp->where('status', 'approved')->map(fn($d) => [
                'nama_diklat' => $d->nama_diklat,
                'tanggal' => $d->tanggal_mulai,
                'jam' => $d->jam_diklat,
                'jenis' => 'Diklat (Mandiri)'
            ]);

            // 2. Data HLC
            $hlc = $karyawan->diklatHlc->where('status', 'approved')->map(fn($d) => [
                'nama_diklat' => $d->hlc->nama_program ?? '-',
                'tanggal' => $d->tanggal_mulai,
                'jam' => $d->jam_diklat,
                'jenis' => 'HLC'
            ]);

            // 3. Data Eksternal
            $eksternal = $karyawan->diklatEksternal->where('status', 'approved')->map(fn($d) => [
                'nama_diklat' => $d->nama_diklat,
                'tanggal' => $d->tanggal_mulai,
                'jam' => $d->jam_diklat,
                'jenis' => 'Eksternal'
            ]);

            // 4. Data Internal Utama
            $internalUtama = $karyawan->diklatInternalUtama
                ->whereNotNull('sertifikat_generated_at')
                ->map(function ($d) {
                    return [
                        'nama_diklat' => $d->periode->detail->nama_diklat ?? 'Diklat Internal',
                        'tanggal' => $d->periode->tanggal ?? '-',
                        'jam' => (int) ($d->periode->aksi->first()->jam_diklat ?? 0),
                        'jenis' => 'Internal'
                    ];
                });

            // Gabungkan SEMUA sumber pelatihan
            $rekamJejak = collect()
                ->concat($internalMandiri)
                ->concat($hlc)
                ->concat($eksternal)
                ->concat($internalUtama)
                ->sortByDesc('tanggal')
                ->values();

            // Jika karyawan tidak punya riwayat pelatihan, tetap masukkan namanya tapi kosongkan pelatihannya
            if ($rekamJejak->isEmpty()) {
                $excelRows->push([
                    'bagian' => $karyawan->bagian ?? '-',
                    'unit_kerja' => $karyawan->unit_kerja ?: 'Tanpa Unit Kerja',
                    'nama_karyawan' => $karyawan->nama_karyawan,
                    'pelatihan' => '-',
                    'tanggal' => '-',
                    'durasi' => 0,
                    'sumber' => '-'
                ]);
            } else {
                // Jika punya, buat 1 baris excel untuk setiap 1 pelatihan
                foreach ($rekamJejak as $jejak) {
                    $excelRows->push([
                        'bagian' => $karyawan->bagian ?? '-',
                        'unit_kerja' => $karyawan->unit_kerja ?: 'Tanpa Unit Kerja',
                        'nama_karyawan' => $karyawan->nama_karyawan,
                        'pelatihan' => $jejak['nama_diklat'],
                        'tanggal' => date('d/m/Y', strtotime($jejak['tanggal'])),
                        'durasi' => $jejak['jam'],
                        'sumber' => $jejak['jenis']
                    ]);
                }
            }
        }

        $fileName = "Laporan_Diklat_Keseluruhan_" . now()->format('Ymd') . ".xlsx";

        return Excel::download(new RekapDatabaseExport($excelRows), $fileName);
    }

    public function exportPerBagian(Request $request)
    {
        $namaBagian = $request->input('bagian');

        if (!$namaBagian) {
            return back()->with('error', 'Nama Bagian tidak disertakan.');
        }

        // Ambil data karyawan HANYA untuk bagian yang dipilih secara eksak (persis)
        $karyawans = Karyawans::with([
            'diklatByNrp',
            'diklatHlc',
            'diklatEksternal',
            'diklatInternalUtama.periode.aksi',
            'diklatInternalUtama.periode.detail'
        ])
        ->where('bagian', $namaBagian) // Exact match
        ->orderBy('unit_kerja')
        ->orderBy('nama_karyawan')
        ->get();

        $excelRows = collect();

        foreach ($karyawans as $karyawan) {
            // 1. Data dari Diklat Mandiri
            $internalMandiri = $karyawan->diklatByNrp->where('status', 'approved')->map(fn($d) => [
                'nama_diklat' => $d->nama_diklat,
                'tanggal' => $d->tanggal_mulai,
                'jam' => $d->jam_diklat,
                'jenis' => 'Diklat (Mandiri)'
            ]);

            // 2. Data HLC
            $hlc = $karyawan->diklatHlc->where('status', 'approved')->map(fn($d) => [
                'nama_diklat' => $d->hlc->nama_program ?? '-',
                'tanggal' => $d->tanggal_mulai,
                'jam' => $d->jam_diklat,
                'jenis' => 'HLC'
            ]);

            // 3. Data Eksternal
            $eksternal = $karyawan->diklatEksternal->where('status', 'approved')->map(fn($d) => [
                'nama_diklat' => $d->nama_diklat,
                'tanggal' => $d->tanggal_mulai,
                'jam' => $d->jam_diklat,
                'jenis' => 'Eksternal'
            ]);

            // 4. Data Internal Utama
            $internalUtama = $karyawan->diklatInternalUtama
                ->whereNotNull('sertifikat_generated_at')
                ->map(function ($d) {
                    return [
                        'nama_diklat' => $d->periode->detail->nama_diklat ?? 'Diklat Internal',
                        'tanggal' => $d->periode->tanggal ?? '-',
                        'jam' => (int) ($d->periode->aksi->first()->jam_diklat ?? 0),
                        'jenis' => 'Internal'
                    ];
                });

            // Gabungkan SEMUA sumber pelatihan
            $rekamJejak = collect()
                ->concat($internalMandiri)
                ->concat($hlc)
                ->concat($eksternal)
                ->concat($internalUtama)
                ->sortByDesc('tanggal')
                ->values();

            if ($rekamJejak->isEmpty()) {
                $excelRows->push([
                    'bagian' => $karyawan->bagian ?? '-',
                    'unit_kerja' => $karyawan->unit_kerja ?: 'Tanpa Unit Kerja',
                    'nama_karyawan' => $karyawan->nama_karyawan,
                    'pelatihan' => '-',
                    'tanggal' => '-',
                    'durasi' => 0,
                    'sumber' => '-'
                ]);
            } else {
                foreach ($rekamJejak as $jejak) {
                    $excelRows->push([
                        'bagian' => $karyawan->bagian ?? '-',
                        'unit_kerja' => $karyawan->unit_kerja ?: 'Tanpa Unit Kerja',
                        'nama_karyawan' => $karyawan->nama_karyawan,
                        'pelatihan' => $jejak['nama_diklat'],
                        'tanggal' => date('d/m/Y', strtotime($jejak['tanggal'])),
                        'durasi' => $jejak['jam'],
                        'sumber' => $jejak['jenis']
                    ]);
                }
            }
        }

        // Nama file dinamis sesuai nama bagian
        $safeBagianName = preg_replace('/[^A-Za-z0-9\-]/', '_', $namaBagian);
        $fileName = "Laporan_Bagian_{$safeBagianName}_" . now()->format('Ymd') . ".xlsx";

        // Menggunakan class Export yang sama dengan yang global
        return Excel::download(new RekapDatabaseExport($excelRows), $fileName);
    }
}
