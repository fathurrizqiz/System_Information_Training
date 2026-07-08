<?php

namespace App\Http\Controllers\RencanaDiklat\RPT;

use App\Http\Controllers\Controller;
use App\Models\AksiDetailInternal;
use App\Models\DokumentasiDetailInternal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Log;

class DokumentasiController extends Controller
{
    public function index($periode_id)
    {
        $aksi = AksiDetailInternal::with('periodeUtama')
            ->where('periode_id', $periode_id)
            ->firstOrFail();

        $detail_id = $aksi->periodeUtama->detail_id;

        $dokumentasi = DokumentasiDetailInternal::where('detail_program_id', $detail_id)->get();

        return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/Dokumentasi/index', [
            'dokumentasi' => $dokumentasi,
            'detail_program_id' => $detail_id
        ]);

    }

    public function storeDokumentasi(Request $request)
    {
        Log::info('Mulai proses upload dokumentasi', [
            'request_data' => $request->all()
        ]);

        $request->validate([
            'detail_program_id' => 'required|exists:detail_internal,id',
            'file_path' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'nama_file' => 'required|string|max:255'
        ]);

        if (!$request->hasFile('file_path')) {
            Log::warning('Upload gagal: file tidak ditemukan');
            return redirect()->back()->withErrors(['file_path' => 'File tidak ditemukan.']);
        }

        try {
            $filePath = $request->file('file_path')->store('dokumentasi', 'public');

            Log::info('File berhasil disimpan', [
                'file_path' => $filePath
            ]);

            DokumentasiDetailInternal::create([
                'detail_program_id' => $request->detail_program_id,
                'nama_file' => $request->nama_file,
                'file_path' => $filePath,
            ]);

            Log::info('Data dokumentasi berhasil disimpan ke database', [
                'detail_program_id' => $request->detail_program_id
            ]);

            return redirect()->back()->with('success', 'Data Berhasil Disimpan!');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan dokumentasi', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors('Terjadi kesalahan saat menyimpan data.');
        }
    }
}
