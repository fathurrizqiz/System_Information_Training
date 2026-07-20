<?php

namespace App\Http\Controllers\KaryawanDiklat;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\HLCManajement;
use App\Models\Karyawans;
use App\Models\RekapJamDiklat;
use App\Models\TargetJamModels;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DiklatController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $karyawan = Karyawans::where('nrp', $user->nrp)->first();

        if (!$karyawan) {
            return abort(403, 'Data karyawan tidak ditemukan untuk user ini.');
        }

        $kategori = $karyawan->klinis_non_klinis;
        $target = TargetJamModels::where('kategori', $kategori)
            ->value('target_jam') ?? 0;

        $bulanIni = now()->month;
        $tahunIni = now()->year;

        $rekap = RekapJamDiklat::where('nrp', $karyawan->nrp)
            ->where('bulan', $bulanIni)
            ->where('tahun', $tahunIni)
            ->first();

        $totalJam = $rekap ? $rekap->total_jam : 0;
        $percentage = $target > 0 ? min(100, ($totalJam / $target) * 100) : 0;

        // Get filter parameters
        $search = $request->input('search');
        $filterDateFrom = $request->input('date_from');
        $filterDateTo = $request->input('date_to');
        $filterStatus = $request->input('status');
        $filterSource = $request->input('source'); // user, admin, eksternal
        $perPage = $request->input('per_page', 10);

        // === Diklat Karyawan (User Input) ===
        $diklatQuery = DiklatKaryawan::where('nrp', $karyawan->nrp);

        if ($search) {
            $diklatQuery->where(function ($q) use ($search) {
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
                    ->orWhere('penyelenggara', 'ILIKE', "%{$search}%");
            });
        }

        if ($filterDateFrom) {
            $diklatQuery->whereDate('tanggal_mulai', '>=', $filterDateFrom);
        }

        if ($filterDateTo) {
            $diklatQuery->whereDate('tanggal_selesai', '<=', $filterDateTo);
        }

        if ($filterStatus) {
            $diklatQuery->where('status', $filterStatus);
        }

        $diklat = $diklatQuery->latest()
            ->paginate($perPage, ['*'], 'diklat_page');

        // === HLC Management (Admin Input) ===
        $adminQuery = HLCManajement::where('nrp', $karyawan->nrp);

        if ($search) {
            $adminQuery->where(function ($q) use ($search) {
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
                    ->orWhere('penyelenggara', 'ILIKE', "%{$search}%");
            });
        }

        if ($filterDateFrom) {
            $adminQuery->whereDate('tanggal_mulai', '>=', $filterDateFrom);
        }

        if ($filterDateTo) {
            $adminQuery->whereDate('tanggal_selesai', '<=', $filterDateTo);
        }

        if ($filterStatus) {
            $adminQuery->where('status', $filterStatus);
        }

        $admin = $adminQuery->latest()
            ->paginate($perPage, ['*'], 'admin_page');

        // === Diklat Eksternal ===
        $eksternalQuery = DiklatEksternal::with('program')->where('nrp', $karyawan->nrp);

        if ($search) {
            $eksternalQuery->whereHas('program', function ($q) use ($search) {
                $q->where('nama_diklat', 'ILIKE', "%{$search}%");
            })->orWhere('penyelenggara', 'ILIKE', "%{$search}%");
        }

        if ($filterDateFrom) {
            $eksternalQuery->whereDate('tanggal_mulai', '>=', $filterDateFrom);
        }

        if ($filterDateTo) {
            $eksternalQuery->whereDate('tanggal_selesai', '<=', $filterDateTo);
        }

        if ($filterStatus) {
            $eksternalQuery->where('status', $filterStatus);
        }

        $eksternal = $eksternalQuery->latest()
            ->paginate($perPage, ['*'], 'eksternal_page');

        return Inertia::render('Diklat/Diklat', [
            'diklat' => $diklat,
            'kategori' => $kategori,
            'target' => $target,
            'totalJam' => $totalJam,
            'percentage' => round($percentage),
            'karyawan' => $karyawan,
            'admin' => $admin,
            'eksternal' => $eksternal,
            'search' => $search,
            'filters' => [
                'date_from' => $filterDateFrom,
                'date_to' => $filterDateTo,
                'status' => $filterStatus,
                'source' => $filterSource,
            ],
        ]);
    }


    public function create()
    {
        $user = auth()->user();

        $karyawan = Karyawans::where('nrp', $user->nrp)->first();

        return Inertia::render('Diklat/create', [
            'karyawan' => $karyawan
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nama_diklat' => 'required|string|max:255',
            'evaluasimateri' => 'nullable|string|max:255',
            'evaluasipengajar' => 'nullable|string|max:255',
            'pengajar' => 'required|string|max:255',
            'diklat' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'jam_diklat' => 'required|integer|min:1',
            'file' => 'required|file|mimes:pdf|max:2048',
        ]);

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai']);

        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        $validated['jam_diklat'] = $validated['jam_diklat'] * $selisihHari;

        $validated['bulan'] = $tanggalMulai->format('n');
        $validated['tahun'] = $tanggalMulai->format('Y');
        $validated['nrp'] = auth()->user()->nrp;
        $validated['status'] = 'pending';

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('diklat_files', 'public');
        }

        $diklat = DiklatKaryawan::create($validated);

        // panggil fungsi rekap bulanan
        $this->updateRekapBulanan($validated['nrp'], $validated['tahun'], $validated['bulan']);

        return redirect()->route('diklat.home')->with('success', 'Data berhasil disimpan!');
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



    public function preview($id)
    {
        $diklat = DiklatKaryawan::findOrFail($id);

        if (!$diklat->file_path || !Storage::disk('public')->exists($diklat->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->file(storage_path('app/public/' . $diklat->file_path));
    }
    public function edit($id)
    {
        $edit = DiklatKaryawan::findOrFail($id);
        return Inertia::render('Diklat/edit', [
            'edit' => $edit
        ]);
    }
    public function update(Request $request, $id)
    {
        // Validasi input
        $validated = $request->validate([
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nama_diklat' => 'required|string|max:255',
            'pengajar' => 'required|string|max:255',
            'diklat' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'jam_diklat' => 'required|integer|min:1',
            'file' => 'nullable|file|mimes:pdf|max:2048',
        ], [
            // Opsional: Kustomisasi pesan error bahasa Indonesia agar lebih user-friendly
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai']);

        if ($tanggalSelesai->lt($tanggalMulai)) {
            return back()->withErrors(['tanggal_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }


        // hasilnya selalu positif dan inklusif (misal 10–11 = 2 hari)
        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        // kalikan jam_diklat dengan lama hari
        $validated['jam_diklat'] = $validated['jam_diklat'] * $selisihHari;


        $diklat = DiklatKaryawan::findOrFail($id);
        // Proses upload file jika ada
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('diklat_files', 'public');
            $validated['file_path'] = $path;
        }

        // Simpan ke database
        $diklat->update($validated);

        if ($diklat->status === 'approved') {
            $this->updateRekapBulanan(
                $diklat->nrp,
                Carbon::parse($diklat->tanggal_mulai)->year,
                Carbon::parse($diklat->tanggal_mulai)->month
            );

        }

        // Redirect dengan pesan sukses
        return redirect()->route('diklat.home')->with('success', 'Data berhasil diperbaharui!');
    }

    public function destroy($id)
    {
        $diklat = DiklatKaryawan::findOrFail($id);

        $tahun = Carbon::parse($diklat->tanggal_mulai)->year;
        $bulan = Carbon::parse($diklat->tanggal_mulai)->month;

        $diklat->delete();

        // update rekap jika status approved
        if ($diklat->status === 'approved') {
            $this->updateRekapBulanan($diklat->nrp, $tahun, $bulan);
        }

        return redirect()
            ->route('diklat.home')
            ->with('success', 'Data berhasil dihapus!');
    }



}
