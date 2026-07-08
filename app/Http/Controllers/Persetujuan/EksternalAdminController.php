<?php

namespace App\Http\Controllers\Persetujuan;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EksternalAdminController extends Controller
{
    public function Persetujuan()
    {
        return Inertia::render('Diklat/Persetujuan/EksternalAdmin/Index');
    }

    public function EksternalAdmin()
    {
        $eksternal = DiklatEksternal::with(['karyawan', 'kehadiran'])
            ->whereIn('status', ['Hadir', 'approved'])
            ->latest()
            ->get()
            ->map(function ($item) {
                 // total hari pelatihan
                $totalHadir = 0;
                $totalTidakHadir = 0;

                foreach ($item->kehadiran as $absen) {

                    $status = strtolower(trim($absen->status));

                    if ($status === 'hadir') {
                        $totalHadir++;
                    } else {
                        $totalTidakHadir++;
                    }
                }

                $item->total_hadir = $totalHadir;
                $item->total_tidak_hadir = $totalTidakHadir;

                $today = now()->toDateString();

                $todayAttendance = $item->kehadiran
                    ->where('tanggal', $today)
                    ->first();

                $item->is_today_absent = $todayAttendance?->status;

                return $item;
            });

        return Inertia::render('Diklat/Persetujuan/EksternalAdmin/Eksternal', [
            'eksternal' => $eksternal
        ]);
    }
}
