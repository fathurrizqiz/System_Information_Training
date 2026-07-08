<?php

namespace App\Http\Controllers\Persetujuan;

use App\Http\Controllers\Controller;
use App\Models\HLCManajement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HLCAdminController extends Controller
{
    public function index()
    {
        $hlc = HLCManajement::with(['karyawan', 'kehadiran'])
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

        return Inertia::render('Diklat/Persetujuan/HLCAdmin/Index', [
            'hlc' => $hlc
        ]);
    }
}
