<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Karyawans;
use App\Models\MasterDataModels;
use App\Models\TargetJam;
use App\Models\TargetJamModels;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MasterDataController extends Controller
{
    public function index()
    {
        // Ambil data karyawan dengan semua relasi diklat
        $data = MasterDataModels::with([
            'diklatByNrp',
            'diklatHlc',
            'diklatEksternal',
            'diklatInternalUtama.periode.aksi',
            'diklatInternalUtama.periode.detail'
        ])
            ->orderBy('nama_karyawan')
            ->get()
            ->map(function ($karyawan) {

                // 1. Data dari Diklat Karyawan (Input Mandiri)
                $internalMandiri = $karyawan->diklatByNrp->where('status', 'approved')->map(fn($d) => [
                    'nama_diklat' => $d->nama_diklat,
                    'tanggal_mulai' => $d->tanggal_mulai,
                    'jam' => $d->jam_diklat,
                    'jenis' => 'Diklat (Mandiri)'
                ]);

                // 2. Data dari HLC
                $hlc = $karyawan->diklatHlc->where('status', 'approved')->map(fn($d) => [
                    'nama_diklat' => $d->nama_diklat,
                    'tanggal_mulai' => $d->tanggal_mulai,
                    'jam' => $d->jam_diklat,
                    'jenis' => 'HLC'
                ]);

                // 3. Data dari Eksternal
                $eksternal = $karyawan->diklatEksternal->where('status', 'approved')->map(fn($d) => [
                    'nama_diklat' => $d->nama_diklat, // Dari appends getNamaDiklatAttribute
                    'tanggal_mulai' => $d->tanggal_mulai,
                    'jam' => $d->jam_diklat,
                    'jenis' => 'Eksternal'
                ]);

                // 4. Update Data dari Internal Utama (Nested Relation)
                $internalUtama = $karyawan->diklatInternalUtama
                    ->whereNotNull('sertifikat_generated_at')
                    ->map(function ($d) {
                    // Ambil nama dari relasi periode -> detail
                    $namaDiklat = $d->periode->detail->nama_diklat ?? 'Diklat Internal';
                    // Ambil jam dari relasi periode -> aksi
                    $jamDiklat = $d->periode->aksi->first()->jam_diklat ?? 0;
                    // Ambil tanggal dari periode
                    $tanggal = $d->periode->tanggal ?? '-';

                    return [
                        'nama_diklat' => $namaDiklat,
                        'tanggal' => $tanggal,
                        'jam' => (int) $jamDiklat,
                        'jenis' => 'Internal'
                    ];
                });

                // Gabungkan SEMUA sumber
                $karyawan->rekam_jejak = collect()
                    ->concat($internalMandiri)
                    ->concat($hlc)
                    ->concat($eksternal)
                    ->concat($internalUtama)
                    ->sortByDesc('tanggal')
                    ->values();

                $karyawan->total_jam_diklat = $karyawan->rekam_jejak->sum('jam');

                return $karyawan;
            });

        // Logika statistik kategori dan target jam (tetap sama)
        $totalKaryawan = MasterDataModels::count();
        $kategoriList = MasterDataModels::distinct()->pluck('klinis_non_klinis')->toArray();
        $targetJam = TargetJamModels::pluck('target_jam', 'kategori')->toArray();

        return Inertia::render('MasterData/index', [
            'data' => $data,
            'totals' => [
                'totalKaryawan' => $totalKaryawan,
                'byCategory' => collect($kategoriList)->mapWithKeys(fn($cat) => [
                    $cat => MasterDataModels::where('klinis_non_klinis', $cat)->count()
                ])
            ],
            'targetJam' => $targetJam,
            'kategoriList' => $kategoriList,
        ]);
    }

    public function pages()
    {
        return Inertia::render('MasterData/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:255',
            'target_jam' => 'required|numeric'
        ]);
        TargetJamModels::create($validated);
        return redirect()->back();
    }


    public function updateTargetJam(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string',
            'target_jam' => 'required|numeric|min:0',
        ]);

        TargetJamModels::updateOrCreate(
            ['kategori' => $validated['kategori']],
            ['target_jam' => $validated['target_jam']]
        );

        return back()->with('success', 'Target jam berhasil diperbarui.');
    }

    public function createkaryawan()
    {
        return Inertia::render('MasterData/addkaryawan');
    }

    public function storekaryawan(Request $request)
    {
        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:255',
            'tmt' => 'required|date',
            'nrp' => 'required|string|unique:karyawans,nrp', // Sebaiknya NRP unik
            'bagian' => 'required|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'klinis_non_klinis' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan', // Gunakan 'in' bukan 'enum' di validate
        ]);

        Karyawans::create($validated);

        return redirect()->back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function updatekaryawan(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_karyawan' => 'required|string|max:255',
            'tmt' => 'required|date',
            'nrp' => 'required|string|max:255|unique:karyawans,nrp,' . $id, // NRP unik kecuali ID sendiri
            'bagian' => 'required|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'klinis_non_klinis' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        ]);

        $karyawan = Karyawans::findOrFail($id);
        $karyawan->update($validated);

        return redirect()->back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroykaryawan($id)
    {
        $karyawan = Karyawans::findOrFail($id);

        $karyawan->diklat()->delete();
        $karyawan->diklatHlc()->delete();

        // Baru hapus karyawannya
        $karyawan->delete();

        return redirect()->back()->with('success', 'Karyawan dan riwayat diklatnya berhasil dihapus.');
    }
}
