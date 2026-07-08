<?php

namespace App\Http\Controllers\report;

use App\Http\Controllers\Controller;
use App\Models\Karyawans;
use App\Models\RekapJamDiklat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonths = $request->input('months', [now()->month]);
        $searchbagian = $request->input('bagian', null);
        $year = now()->year;

        if (!is_array($selectedMonths)) {
            $selectedMonths = [$selectedMonths];
        }

        $countSelectedMonths = count($selectedMonths);

        // 1. Ambil Aktual Jam per Bagian (untuk tabel utama)
        $aktualPerBagian = Karyawans::join('rekap_jam_diklat', 'karyawans.nrp', '=', 'rekap_jam_diklat.nrp')
            ->whereIn('rekap_jam_diklat.bulan', $selectedMonths)
            ->where('rekap_jam_diklat.tahun', $year)
            ->groupBy('karyawans.bagian')
            ->pluck(\DB::raw('SUM(rekap_jam_diklat.total_jam) as total_aktual'), 'bagian');

        // 2. Ambil List Karyawan Detail per Bagian
        // Kita ambil jam aktual masing-masing karyawan di periode terpilih
        $karyawanDetail = Karyawans::leftJoin('rekap_jam_diklat', function ($join) use ($selectedMonths, $year) {
            $join->on('karyawans.nrp', '=', 'rekap_jam_diklat.nrp')
                ->whereIn('rekap_jam_diklat.bulan', $selectedMonths)
                ->where('rekap_jam_diklat.tahun', $year);
        })
            ->leftJoin('target_jam_datamaster', 'karyawans.klinis_non_klinis', '=', 'target_jam_datamaster.kategori')
            ->select(
                'karyawans.bagian',
                'karyawans.nrp',
                'karyawans.nama_karyawan',
                \DB::raw('SUM(COALESCE(rekap_jam_diklat.total_jam, 0)) as jam_aktual'),
                \DB::raw('MAX(target_jam_datamaster.target_jam) as target_dasar')
            )
            ->groupBy('karyawans.bagian', 'karyawans.nrp', 'karyawans.nama_karyawan')
            ->get()
            ->groupBy('bagian'); // Kelompokkan berdasarkan bagian agar mudah di-map

        // 3. Ambil Target per Bagian
        $queryTarget = Karyawans::leftJoin('target_jam_datamaster', 'karyawans.klinis_non_klinis', '=', 'target_jam_datamaster.kategori')
            ->select('karyawans.bagian')
            ->selectRaw('COUNT(karyawans.nrp) as total_karyawan')
            ->selectRaw('SUM(target_jam_datamaster.target_jam) as total_target_jam_dasar');

        if ($searchbagian) {
            $queryTarget->where('karyawans.bagian', 'ILIKE', '%' . $searchbagian . '%');
        }

        $targetKaryawanPerBagian = $queryTarget->groupBy('karyawans.bagian')->get();

        // 4. Gabungkan Data Final
        $dataFinal = $targetKaryawanPerBagian->map(function ($row) use ($aktualPerBagian, $countSelectedMonths, $karyawanDetail) {
            $targetTotal = $row->total_target_jam_dasar * $countSelectedMonths;
            $aktual = $aktualPerBagian[$row->bagian] ?? 0;

            // Ambil detail karyawan untuk bagian ini
            $listKaryawan = collect($karyawanDetail->get($row->bagian))->map(function ($k) use ($countSelectedMonths) {
                $t_individu = $k->target_dasar * $countSelectedMonths;
                return [
                    'nrp' => $k->nrp,
                    'nama' => $k->nama_karyawan,
                    'aktual' => (float) $k->jam_aktual,
                    'target' => round($t_individu, 2),
                    'persentase' => $t_individu > 0 ? round(($k->jam_aktual / $t_individu) * 100, 2) : 0
                ];
            });

            return [
                'kategori' => $row->bagian,
                'totalKaryawan' => $row->total_karyawan,
                'totalTargetJam' => round($targetTotal, 2),
                'aktualJam' => (float) $aktual,
                'persentase' => $targetTotal > 0 ? round(($aktual / $targetTotal) * 100, 2) : 0,
                'karyawans' => $listKaryawan // Inilah data drill-down nya
            ];
        });

        $targetAll = $dataFinal->sum('totalTargetJam');
        $rekapJamTerpilih = RekapJamDiklat::whereIn('bulan', $selectedMonths)
            ->where('tahun', $year)
            ->sum('total_jam');

        return Inertia::render('report/Report', [
            'filters' => [
                'months' => $selectedMonths,
                'year' => $year,
                'bagian' => $searchbagian,
            ],
            'totalPerKategori' => $dataFinal,
            'totalJamDiklat' => (float) $rekapJamTerpilih,
            'targetAll' => round($targetAll, 2),
        ]);
    }
}
