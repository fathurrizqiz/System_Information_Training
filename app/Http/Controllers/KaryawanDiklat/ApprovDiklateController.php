<?php

namespace App\Http\Controllers\KaryawanDiklat;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\HLCManajement;
use App\Models\RekapJamDiklat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ApprovDiklateController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $diklat = DiklatKaryawan::query()
            ->with([
                'karyawan' => function ($query) {
                    $query->select('nrp', 'nama_karyawan'); // Ambil kolom yang diperlukan saja
                }
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_diklat', 'ILIKE', "%{$search}%")
                        ->orWhere('nrp', 'ILIKE', "%{$search}%")
                        // Jika ingin cari berdasarkan nama karyawan juga:
                        ->orWhereHas('karyawan', function ($q) use ($search) {
                            $q->where('nama_karyawan', 'ILIKE', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query, $status) {
            $query->where('status', $status);
        })
            ->latest()
            ->get()
            ->map(function ($item) {
                $item->link_file = $item->file_path
                    ? \Storage::url(str_replace('public/', '', $item->file_path))
                    : null;

                // Tambahkan nama_karyawan ke level atas agar mudah diakses di Vue
                $item->nama_karyawan = $item->karyawan->nama_karyawan ?? 'Tidak Ditemukan';

                return $item;
            });

        return Inertia::render('Diklat/Approve/index', [
            'diklat' => $diklat,
            'filters' => ['search' => $search, 'status' => $status]
        ]);
    }

    public function updateRekapBulanan($nrp, $tahun, $bulan)
    {
        $totalJam = (
            DiklatKaryawan::where('nrp', $nrp)->where('status', 'approved')
                ->whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulan)
                ->sum('jam_diklat')
        ) +
            (
                HLCManajement::where('nrp', $nrp)->where('status', 'approved')
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->sum('jam_diklat')
            ) +
            (
                DiklatEksternal::where('nrp', $nrp)->where('status', 'approved')
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->sum('jam_diklat')
            );

        RekapJamDiklat::updateOrCreate(
            [
                'nrp' => $nrp,
                'tahun' => $tahun,
                'bulan' => $bulan
            ],
            [
                'total_jam' => $totalJam
            ]
        );
    }


    public function approve($id)
    {
        $diklat = DiklatKaryawan::findOrFail($id);

        if ($diklat->status !== 'pending') {
            return back()->with('error', 'Data sudah diproses sebelumnya.');
        }

        $diklat->update([
            'status' => 'approved'
        ]);



        $this->updateRekapBulanan(
            $diklat->nrp,
            $diklat->tanggal_mulai->format('Y'),
            $diklat->tanggal_mulai->format('n')
        );


        return back()->with('success', 'Diklat berhasil disetujui!');
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => 'required|string|max:255'
        ]);

        $diklat = DiklatKaryawan::findOrFail($id);

        if ($diklat->status !== 'pending') {
            return back()->with('error', 'Data sudah diproses sebelumnya.');
        }

        $diklat->update([
            'status' => 'rejected',
            'alasan_penolakan' => $validated['alasan_penolakan']
        ]);

        $this->updateRekapBulanan(
            $diklat->nrp,
            $diklat->tanggal_mulai->format('Y'),
            $diklat->tanggal_mulai->format('n')
        );


        return back()->with('success', 'Diklat telah ditolak!');
    }


}
