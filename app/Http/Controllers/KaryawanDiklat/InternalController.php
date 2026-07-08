<?php

namespace App\Http\Controllers\KaryawanDiklat;

use App\Http\Controllers\Controller;
use App\Models\AksiDetailInternal;
use App\Models\PeriodeBagianDetailInternal;
use App\Models\RekapJamDiklat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InternalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $karyawan = \DB::table('karyawans')
            ->where('nrp', $user->nrp)
            ->first();

        if (!$karyawan) {
            return abort(403, 'Data karyawan tidak ditemukan.');
        }

        $search = $request->input('search');
        // Ambil parameter filter baru
        $filterDateFrom = $request->input('date_from');
        $filterDateTo = $request->input('date_to');
        $filterStatus = $request->input('status_sertifikat'); // 'tersedia' atau 'belum'

        // Tambahkan 'presensi' ke dalam with() dan filter berdasarkan nrp user
        $query = PeriodeBagianDetailInternal::with([
            'periode.detail',
            'aksi',
            'presensi' => function ($q) use ($user) {
                $q->where('nrp', $user->nrp);
            }
        ])->where('nrp', $user->nrp);

        // --- LOGIKA FILTER SEARCH (EXISTING) ---
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('periode.detail', function ($q2) use ($search) {
                    $q2->where('nama_diklat', 'ILIKE', "%{$search}%");
                })
                    ->orWhereHas('periode', function ($q2) use ($search) {
                        $q2->where('nama_pengajar', 'ILIKE', "%{$search}%");
                    });
            });
        }

        // --- LOGIKA FILTER BARU ---

        // 1. Filter Tanggal (Mengambil dari relasi 'periode')
        if ($filterDateFrom) {
            $query->whereHas('periode', function ($q) use ($filterDateFrom) {
                $q->whereDate('tanggal', '>=', $filterDateFrom);
            });
        }

        if ($filterDateTo) {
            $query->whereHas('periode', function ($q) use ($filterDateTo) {
                $q->whereDate('tanggal', '<=', $filterDateTo);
            });
        }

        // 2. Filter Status Sertifikat
        if ($filterStatus) {
            if ($filterStatus === 'tersedia') {
                $query->whereNotNull('sertifikat_path');
            } elseif ($filterStatus === 'belum') {
                $query->whereNull('sertifikat_path');
            }
        }

        $pesertaList = $query->get()->map(function ($peserta) {
            return [
                'id' => $peserta->id,
                'jam_diklat' => $peserta->periode?->detail?->aksi?->jam_diklat ?? 'Pelatihan belum dimulai',
                'nama_diklat' => $peserta->periode?->detail?->nama_diklat,
                'nama_pengajar' => $peserta->periode?->nama_pengajar ?? '-',
                'tanggal_diklat' => $peserta->periode?->tanggal ?? null, // Kirim tanggal ke frontend untuk display
                'pree_done_at' => $peserta->pree_done_at,
                'post_done_at' => $peserta->post_done_at,
                'sertifikat_path' => $peserta->sertifikat_path,
                'is_hadir' => $peserta->presensi ? true : false,
            ];
        });

        return Inertia::render('Diklat/Internal/index', [
            'internal' => $pesertaList,
            'filters' => [
                'date_from' => $filterDateFrom,
                'date_to' => $filterDateTo,
                'status_sertifikat' => $filterStatus,
            ],
        ]);
    }


}
