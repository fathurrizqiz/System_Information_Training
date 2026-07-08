<?php

namespace App\Http\Controllers\RencanaDiklat\RPT;

use App\Http\Controllers\Controller;
use App\Models\AksiDetailInternal;
use App\Models\DetailInternal;
use App\Models\Karyawans;
use App\Models\PendidikanFormalModels;
use App\Models\PeriodeUtama;
use App\Models\TestToken;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiklatInternalController extends Controller
{
    public function index()
    {
        $programs = PendidikanFormalModels::with(['details.aksi'])->get();
        return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/index', [
            'programs' => $programs,
        ]);
    }


    public function storeProgram(Request $request)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'tahun' => 'nullable|string|max:255'
        ]);
        PendidikanFormalModels::create($validated);
        return redirect()->back();
    }

    public function storeDetail(Request $request)
    {
        $validate = $request->validate([
            'program_id' => 'required|exists:program_internal,id',
            'nama_diklat' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
            'pengajar' => 'required|string|max:255',
        ]);

        DetailInternal::create($validate);
        return redirect()->back();
    }

    public function destroyProgram($id)
    {
        $delete = PendidikanFormalModels::findOrFail($id);
        $delete->delete();
        return redirect()->route('PF.index');
    }

    public function destroyDetail($id)
    {
        $delete = DetailInternal::findOrFail($id);
        $delete->delete();
        return redirect()->route('PF.index');
    }

    public function aksi($id)
    {
        $detail = DetailInternal::findOrFail($id);
        $periode = PeriodeUtama::where('detail_id', $id)->get();

        $periodeIds = $periode->pluck('id');

        // Cek apakah ADA periode yang sedang berjalan
        $aksi = AksiDetailInternal::whereIn('periode_id', $periodeIds)
            ->whereNull('ended_at')
            ->first();

        $runningPeriodeId = $aksi ? $aksi->periode_id : null;

        // \Log::info('DEBUG aksi', [
        //     'detail_id' => $id,
        //     'periode_count' => $periode->count(),
        //     'periodeIds' => $periodeIds->toArray(),
        //     'aksi_exists' => AksiDetailInternal::whereIn('periode_id', $periodeIds)->whereNull('ended_at')->exists(),
        // ]);

        $tokenLink = null;
        if ($runningPeriodeId) { // ✅ Gunakan runningPeriodeId, bukan $aksi saja
            $tokens = TestToken::where('periode_id', $runningPeriodeId) // ✅ PERBAIKAN UTAMA
                ->whereIn('type', ['pree', 'post', 'evaluasi'])
                ->get()
                ->keyBy('type');

            $tokenLink = [
                'pree' => isset($tokens['pree']) ? url("/test/token/pree/{$tokens['pree']->token}") : null,
                'post' => isset($tokens['post']) ? url("/test/token/post/{$tokens['post']->token}") : null,
                'evaluasi' => isset($tokens['evaluasi']) ? url("/test/token/evaluasi/{$tokens['evaluasi']->token}") : null,
            ];
        }

        return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/aksilanjut', [
            'data' => $detail,
            'detail_id' => $detail->id,
            'periode' => $periode,
            'ValidasiStart' => AksiDetailInternal::whereIn('periode_id', $periodeIds)
                ->pluck('periode_id')
                ->map(fn($id) => (string) $id)
                ->toArray(),
            'isRunning' => $aksi !== null,
            'runningPeriodeId' => $runningPeriodeId,
            'token_link' => $tokenLink,
        ]);
    }

    public function periode($id)
    {
        // Ambil detail lengkap + relasi periode
        $detail = DetailInternal::with('periodes')->findOrFail($id);

        $karyawan = Karyawans::all();

        return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/Periode/index', [
            'periode' => $detail->periodes,
            'karyawan' => $karyawan,
            'detail_id' => $detail->id,
        ]);
    }


    public function storePeriode(Request $request)
    {
        $validate = $request->validate([
            'detail_id' => 'required|exists:detail_internal,id',
            'tanggal' => 'required|date',
            'nama_pengajar' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
        ]);

        \Log::info('Data yang akan disimpan:', $validate);

        $periode = PeriodeUtama::create($validate);

        \Log::info('Data tersimpan dengan ID:', ['id' => $periode->id, 'detail_id' => $periode->detail_id]);

        return redirect()->back()->with('success', 'Data Periode Berhasil ditambah!');
    }

    public function destroyPeriod($id)
    {
        $delete = PeriodeUtama::findOrFail($id);
        $delete->delete();
        return redirect()->back();
    }
}
