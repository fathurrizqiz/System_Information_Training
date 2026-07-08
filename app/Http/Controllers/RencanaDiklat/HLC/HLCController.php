<?php

namespace App\Http\Controllers\RencanaDiklat\HLC;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\HLCAbsenModel;
use App\Models\HLCManajement;
use App\Models\Karyawans;
use App\Models\ProgramHlc;
use App\Models\RekapJamDiklat;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HLCController extends Controller
{
    public function index()
    {
        $karyawan = Karyawans::all();
        $program = ProgramHlc::with('hlc.karyawan')->orderBy('tahun', 'desc')->get();


        return Inertia::render('RencanaDiklat/HLC/index', [
            'program' => $program,
            'karyawans' => $karyawan,

        ]);
    }

    public function updateRekapBulanan($nrp, $tahun, $bulan)
    {
        // Log::info('Memanggil updateRekapBulanan well', compact('nrp', 'tahun', 'bulan'));
        // 1. Diklat Karyawan (eksternal lama)
        $jamDiklatKaryawan = DiklatKaryawan::where('nrp', $nrp)
            ->where('status', 'approved')
            ->whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->sum('jam_diklat');

        // 2. HLC
        $jamHLC = HLCManajement::where('nrp', $nrp)
            ->where('status', 'approved')
            ->whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->sum('jam_diklat');

        // 3. Diklat Internal: JOIN ketiga tabel
        $jamInternal = DB::table('periode_bagian_detail_internal as p')
            ->join('periode_detail_internal as periode', 'p.periode_id', '=', 'periode.id')
            ->join('aksi_detail_internal as aksi', 'aksi.periode_id', '=', 'periode.id')
            ->where('p.nrp', $nrp)
            ->whereNotNull('p.post_done_at')
            ->whereYear('periode.tanggal', $tahun)
            ->whereMonth('periode.tanggal', $bulan)
            ->sum('aksi.jam_diklat');

        $jamDiklatEksternal = DiklatEksternal::where('nrp', $nrp)
            ->where('status', 'approved')
            ->whereYear('tanggal_mulai', $tahun)
            ->whereMonth('tanggal_mulai', $bulan)
            ->sum('jam_diklat');


        $totalJam = $jamDiklatKaryawan + $jamHLC + $jamInternal + $jamDiklatEksternal;

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

    public function storeProgram(Request $request)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|digits:4'
        ]);

        ProgramHlc::create($validated);
        return redirect()->route('diklat.hlc.admin');
    }
    public function storeDetail(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:program_diklat_hlc,id',
            'nama_diklat' => 'nullable|string',
            'pengajar' => 'nullable|string',
            'penyelenggara' => 'nullable|string',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nrp' => 'nullable|string|max:255',
            'jam_diklat' => 'nullable|integer',
            'dokumen' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg|max:2048'
        ], [
            // Opsional: Kustomisasi pesan error bahasa Indonesia agar lebih user-friendly
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validated['jam_diklat'] > 9) {
            return redirect()->back()->with('error', 'Jam diklat per hari tidak boleh lebih dari 9 jam.');
        }

        // Default approved karena admin yang input
        $validated['status'] = 'menunggu_persetujuan';

        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai']);
        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        $validated['jam_diklat'] = $validated['jam_diklat'] * $selisihHari;

        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');

            // Membuat nama file unik: nrp_timestamp.ekstensi (contoh: 005191201_168456789.pdf)
            $namaFile = ($validated['nrp'] ?? 'karyawan') . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Simpan file ke dalam folder 'public/dokumen_diklat'
            $path = $file->storeAs('dokumen_diklat', $namaFile, 'public');

            // Simpan path/nama file ke array validate untuk dimasukkan ke database
            $validated['dokumen'] = $path;
        }

        // 2. Create data
        HLCManajement::create($validated);

        // Panggil rekap
        // $this->updateRekapBulanan(
        //     $hlc->nrp,
        //     date('Y', strtotime($hlc->tanggal_mulai)),
        //     date('n', strtotime($hlc->tanggal_mulai))
        // );
        // PINDAH

        return redirect()->route('diklat.hlc.admin');
    }
    public function updateProgram(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_program' => 'required|string|max:255',
            'tahun' => 'required|digits:4'
        ]);

        ProgramHlc::where('id', $id)->update($validated);
        return redirect()->back()->with('success', 'Program diperbarui');
    }


    public function destroyProgram($id)
    {
        $delete = ProgramHlc::findOrFail($id);
        $delete->delete();

        return redirect()->route('diklat.hlc.admin');
    }

    public function updateDetail(Request $request, $id)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:program_diklat_hlc,id',
            'nama_diklat' => 'nullable|string',
            'pengajar' => 'nullable|string',
            'penyelenggara' => 'nullable|string',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'nrp' => 'nullable|string|max:255',
            'jam_diklat' => 'nullable|integer', // Ini adalah jam per hari dari input
            'dokumen' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg|max:2048'
        ], [
            // Opsional: Kustomisasi pesan error bahasa Indonesia agar lebih user-friendly
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $hlc = HLCManajement::findOrFail($id);

        // Hitung ulang total jam diklat (Jam per hari * Selisih hari)
        $tanggalMulai = Carbon::parse($validated['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validated['tanggal_selesai']);

        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        // Total jam = (input jam per hari) * jumlah hari
        $validated['jam_diklat'] = ($validated['jam_diklat'] ?? 0) * $selisihHari;

        $hlc->update($validated);


        return redirect()->route('diklat.hlc.admin')->with('success', 'Detail diklat berhasil diperbarui.');
    }

    public function destroyDetail($id)
    {
        $hlc = HLCManajement::findOrFail($id);
        $nrp = $hlc->nrp;
        $tahun = date('Y', strtotime($hlc->tanggal_mulai));
        $bulan = date('n', strtotime($hlc->tanggal_mulai));

        $hlc->delete();

        // Jalankan rekap ulang setelah hapus agar total jam di rekap berkurang
        // $this->updateRekapBulanan($nrp, $tahun, $bulan);

        return redirect()->route('diklat.hlc.admin')->with('success', 'Data diklat berhasil dihapus.');
    }


    // SISI USER INBOX

    public function hadirHLC($id)
    {
        $nrp = auth()->user()->nrp;

        // ambil data pelatihan
        $diklat = HLCManajement::where('id', $id)
            ->where('nrp', $nrp)
            ->firstOrFail();

        // cek apakah hari ini sudah absen
        $sudahAbsen = HLCAbsenModel::where(
            'diklat_hlc_id',
            $diklat->id
        )
            ->whereDate('tanggal', Carbon::today())
            ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Anda sudah absen hari ini.');
        }

        // simpan absensi
        HLCAbsenModel::create([
            'diklat_hlc_id' => $diklat->id,
            'tanggal' => Carbon::today(),
            'status' => 'hadir',
        ]);

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    public function uploadBuktiHLC(Request $request, $id)
    {


        $request->validate([
            'dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $diklat = HLCManajement::findOrFail($id);

        // hanya peserta aktif yang boleh upload
        if ($diklat->status !== 'Setuju') {
            return back()->with(
                'error',
                'Peserta tidak dapat upload bukti.'
            );
        }

        // validasi hanya boleh upload di hari terakhir
        if (
            now()->toDateString() !==
            Carbon::parse($diklat->tanggal_selesai)->toDateString()
        ) {
            return back()->with(
                'error',
                'Upload bukti hanya bisa dilakukan di hari terakhir.'
            );
        }

        if ($request->hasFile('dokumen')) {

            $file = $request->file('dokumen');

            $namaFile =
                $diklat->nrp . '_' . time() . '.' .
                $file->getClientOriginalExtension();

            $path = $file->storeAs(
                'bukti_kehadiran',
                $namaFile,
                'public'
            );

            $diklat->update([
                'bukti_hadir' => $path,
                'status' => 'Hadir',
            ]);
        }

        return back()->with(
            'success',
            'Bukti berhasil dikirim dan menunggu verifikasi admin.'
        );
    }

    // konfirmasi admin

    public function approveKehadiran($id)
    {
        $hlc = HLCManajement::findOrFail($id);

        $hlc->update([
            'status' => 'approved'
        ]);

        // BARU REKAP DIPANGGIL
        $this->updateRekapBulanan(
            $hlc->nrp,
            date('Y', strtotime($hlc->tanggal_mulai)),
            date('n', strtotime($hlc->tanggal_mulai))
        );

        return back()->with(
            'success',
            'Kehadiran berhasil diverifikasi.'
        );
    }

}
