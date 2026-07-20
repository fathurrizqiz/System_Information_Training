<?php

namespace App\Http\Controllers\RencanaDiklat\RPT;

use App\Http\Controllers\Controller;
use App\Models\DetailInternal;
use App\Models\Karyawans;
use App\Models\PeriodeBagianDetailInternal;
use App\Models\PeriodeUtama;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Log;

class DetailPeriodeController extends Controller
{
    public function index(Request $request, $detail_id)
    {
        $detail = DetailInternal::findOrFail($detail_id);

        $periodes = PeriodeUtama::where('detail_id', $detail_id)
            ->orderBy('tanggal')
            ->get();

        // Ambil semua bagian unik
        $bagians = Karyawans::select('nama_karyawan', 'nrp', 'bagian')
            ->whereNotNull('bagian')
            ->whereNotNull('nama_karyawan')
            ->whereNotNull('nrp')
            ->distinct()
            ->orderBy('bagian')
            ->orderBy('nama_karyawan')
            ->orderBy('nrp')
            ->get();



        // Siapkan variabel untuk karyawan yang akan ditampilkan
        $rows = PeriodeBagianDetailInternal::with('karyawan')
            ->when($request->periode_id, function ($query, $periodeId) {
                return $query->where('periode_id', $periodeId);
            })
            ->get()
            ->filter(function ($item) {
                return $item->karyawan !== null;
            })
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_karyawan' => $item->karyawan->nama_karyawan,
                    'tmt' => $item->karyawan->tmt,
                    'nrp' => $item->karyawan->nrp,
                    'bagian' => $item->karyawan->bagian,
                    'unit_kerja' => $item->karyawan->unit_kerja,
                    'posisi_jabatan' => $item->karyawan->posisi_jabatan,
                    'klinis_non_klinis' => $item->karyawan->klinis_non_klinis,
                    'jenis_kelamin' => $item->karyawan->jenis_kelamin,
                ];
            })->values();
        ;
        // Log::info('PERIODE ID MASUK', [
        //     'request_periode_id' => $request->periode_id
        // ]);
        // Log::info('DATA DI DB', [
        //     'count' => PeriodeBagianDetailInternal::where('periode_id', $request->periode_id)->count()
        // ]);


        return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/DetailPeriode/index', [
            'detail' => $detail,
            'periodes' => $periodes,
            'rows' => $rows,
            'bagians' => $bagians,
            'selectedPeriodeId' => $request->periode_id,
            'selectedBagian' => $request->bagian ?? [],
        ]);
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'bagian' => 'nullable|array',
            'bagian.*' => 'string',

            'nama_karyawan' => 'nullable|array',
            'nama_karyawan.*' => 'string',

            'detail_program_id' => 'required|integer',
            'periode_id' => 'required|integer',
        ]);

        $bagianDipilih = $validated['bagian'] ?? [];
        $namaDipilih = $validated['nama_karyawan'] ?? [];

        if (empty($bagianDipilih) && empty($namaDipilih)) {
            return back()->withErrors([
                'bagian' => 'Pilih minimal satu bagian atau satu karyawan.'
            ]);
        }

        // Ambil data karyawan berdasarkan bagian ATAU nama
        $karyawan = Karyawans::query()
            ->when(!empty($bagianDipilih), function ($q) use ($bagianDipilih) {
                $q->whereIn('bagian', $bagianDipilih);
            })
            ->when(!empty($namaDipilih), function ($q) use ($namaDipilih) {
                $q->orWhereIn('nama_karyawan', $namaDipilih);
            })
            ->get()
            ->unique('nrp')
            ->values();

        foreach ($karyawan as $k) {

            // Cek apakah sudah pernah dimasukkan
            $sudahAda = PeriodeBagianDetailInternal::where([
                'detail_program_id' => $validated['detail_program_id'],
                'periode_id' => $validated['periode_id'],
                'nrp' => $k->nrp,
            ])->exists();

            if ($sudahAda) {
                continue;
            }

            PeriodeBagianDetailInternal::create([
                'detail_program_id' => $validated['detail_program_id'],
                'periode_id' => $validated['periode_id'],
                'nama_karyawan' => $k->nama_karyawan,
                'tmt' => $k->tmt,
                'nrp' => $k->nrp,
                'bagian' => $k->bagian,
                'unit_kerja' => $k->unit_kerja,
                'posisi_jabatan' => $k->posisi_jabatan,
                'klinis_non_klinis' => $k->klinis_non_klinis,
                'jenis_kelamin' => $k->jenis_kelamin,
            ]);
        }

        return redirect()
            ->route('aksi-internal', ['id' => $validated['detail_program_id']])
            ->with('success', 'Data berhasil disimpan');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || count($ids) === 0) {
            return back()->withErrors('Tidak ada data yang dipilih');
        }

        Log::info('Bulk delete PeriodeBagianDetailInternal', ['ids' => $ids]);

        PeriodeBagianDetailInternal::whereIn('id', $ids)->delete();

        return back()->with('success', 'Data terpilih berhasil dihapus!');
    }

}
