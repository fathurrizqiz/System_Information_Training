<?php

namespace App\Http\Controllers\RencanaDiklat\RPT;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\EksternalAbsenModel;
use App\Models\HLCManajement;
use App\Models\Karyawans;
use App\Models\ProgramEksternal;
use App\Models\RekapJamDiklat;
use App\Models\WaTemplate;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NonFormalController extends Controller
{
    public function index()
    {
        $karyawan = Karyawans::all();
        $program = ProgramEksternal::with('eksternal.karyawan')->latest()->get();
        $templates = WaTemplate::all(['id', 'nama_template', 'slug']);
        return Inertia::render('RencanaDiklat/RPT/PendidikanNonFormal/index', [
            'karyawan' => $karyawan,
            'program' => $program,
           'templates' => $templates,
        ]);
    }
    public function storeProgram(Request $request)
    {
        $validate = $request->validate([
            'nama_diklat' => 'required|string|max:255',
            'tahun' => 'required|string|max:255',
        ]);
        ProgramEksternal::create($validate);
        return redirect()->route('Diklat.eksternal');
    }
    public function storeDetail(Request $request)
    {
        $validate = $request->validate([
            'program_id' => 'required|exists:program_diklat_eksternal,id',
            'nama_karyawan' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date|after_or_equal:today',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_diklat' => 'required|integer',
            'penyelenggara' => 'nullable|string|max:255',
            'nrp' => 'nullable|string|max:255',
            'dokumen' => 'required|file|mimes:pdf,jpg,jpeg|max:2048',
        ], [
            // Opsional: Kustomisasi pesan error bahasa Indonesia agar lebih user-friendly
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validate['jam_diklat'] > 9) {
            return redirect()->back()->with('error', 'Jam diklat per hari tidak boleh lebih dari 10 jam.');
        }

        // Default approved karena admin yang input
        $validate['status'] = 'menunggu_persetujuan';

        $tanggalMulai = Carbon::parse($validate['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validate['tanggal_selesai']);

        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        $validate['jam_diklat'] = $validate['jam_diklat'] * $selisihHari;

        // Proses Upload Dokumen jika file valid
        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');

            // Membuat nama file unik: nrp_timestamp.ekstensi (contoh: 005191201_168456789.pdf)
            $namaFile = ($validate['nrp'] ?? 'karyawan') . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Simpan file ke dalam folder 'public/diklat_eksternal'
            $path = $file->storeAs('diklat_eksternal_', $namaFile, 'public');

            // Simpan path/nama file ke array validate untuk dimasukkan ke database
            $validate['dokumen'] = $path;
        }

        $eksternal = DiklatEksternal::create($validate);

        // Panggil rekap
        // $this->updateRekapBulanan(
        //     $eksternal->nrp,
        //     date('Y', strtotime($eksternal->tanggal_mulai)),
        //     date('n', strtotime($eksternal->tanggal_mulai))
        // );
        return redirect()->route('Diklat.eksternal');
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

    public function updateProgram(Request $request, $id)
    {
        $validate = $request->validate([
            'nama_diklat' => 'required|string|max:255',
            'tahun' => 'required|string|max:255',
        ]);
        ProgramEksternal::findOrFail($id)->update($validate);
        return redirect()->back()->with('success', 'Program diperbarui');
    }

    public function updateDetail(Request $request, $id)
    {
        $diklat = DiklatEksternal::findOrFail($id);

        $validate = $request->validate([
            'program_id' => 'required|exists:program_diklat_eksternal,id',
            'nama_karyawan' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date|after_or_equal:today',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_diklat' => 'required|integer',
            'penyelenggara' => 'required|string|max:255',
            'nrp' => 'nullable|string|max:255',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
        ], [
            // pesan error
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        // status default
        $validate['status'] = 'menunggu_persetujuan';

        // Hitung total jam
        $tanggalMulai = Carbon::parse($validate['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validate['tanggal_selesai']);

        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;

        $validate['jam_diklat'] =
            $validate['jam_diklat'] * $selisihHari;

        // Upload dokumen baru
        if ($request->hasFile('dokumen')) {

            // Hapus file lama jika ada
            if ($diklat->dokumen && Storage::disk('public')->exists($diklat->dokumen)) {
                Storage::disk('public')->delete($diklat->dokumen);
            }

            $file = $request->file('dokumen');

            $namaFile =
                ($validate['nrp'] ?? 'karyawan')
                . '_' . time()
                . '.' . $file->getClientOriginalExtension();

            $path = $file->storeAs(
                'dokumen_diklat',
                $namaFile,
                'public'
            );

            $validate['dokumen'] = $path;
        }

        // Update data
        $diklat->update($validate);

        return redirect()->back()->with([
            'success' => 'Data berhasil diperbarui'
        ]);
    }

    public function destroyDetail($id)
    {
        $eksternal = DiklatEksternal::findOrFail($id);
        $eksternal->delete();

        // Update rekap
        $this->updateRekapBulanan(
            $eksternal->nrp,
            date('Y', strtotime($eksternal->tanggal_mulai)),
            date('n', strtotime($eksternal->tanggal_mulai))
        );

        return redirect()->back()->with('success', 'Detail diklat eksternal dihapus');
    }
    public function destroyProgram($id)
    {
        $program = ProgramEksternal::findOrFail($id);
        $program->delete();
        return redirect()->back()->with('success', 'Program diklat eksternal dihapus');
    }

    // SISI USER

    // ABSEN
    public function hadir($id)
    {
        $nrp = auth()->user()->nrp;

        // ambil data pelatihan
        $diklat = DiklatEksternal::where('id', $id)
            ->where('nrp', $nrp)
            ->firstOrFail();

        // cek apakah hari ini sudah absen
        $sudahAbsen = EksternalAbsenModel::where(
            'diklat_eksternal_id',
            $diklat->id
        )
            ->whereDate('tanggal', Carbon::today())
            ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Anda sudah absen hari ini.');
        }

        // simpan absensi
        EksternalAbsenModel::create([
            'diklat_eksternal_id' => $diklat->id,
            'tanggal' => Carbon::today(),
            'status' => 'hadir',
        ]);

        return back()->with('success', 'Absensi berhasil disimpan.');
    }


    public function uploadBukti(Request $request, $id)
    {

        $request->validate([
            'dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $diklat = DiklatEksternal::findOrFail($id);

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
        $hlc = DiklatEksternal::findOrFail($id);

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


    // ADMIN UPLOAD
    public function indexbyADMIN()
    {
        $karyawan = Karyawans::all();
        $program = ProgramEksternal::with('eksternal.karyawan')->latest()->get();
        
        return Inertia::render('RencanaDiklat/RPT/PendidikanNonFormal/IndexAdmin', [
            'karyawan' => $karyawan,
            'program' => $program,
           
        ]);
    }
    public function previewAdminEksternal($id)
    {
        $diklat = DiklatEksternal::findOrFail($id);

        if (!$diklat->file_path || !Storage::disk('public')->exists($diklat->bukti_hadir)) {
            abort(404, 'File tidak ditemukan.');
        }
      

        return response()->file(storage_path('app/public/' . $diklat->bukti_hadir));
    }
    public function storeProgrambyADMIN(Request $request)
    {
        $validate = $request->validate([
            'nama_diklat' => 'required|string|max:255',
            'tahun' => 'required|string|max:255',
        ]);
        ProgramEksternal::create($validate);
        return redirect()->route('Diklat.eksternal');
    }
    public function storeDetailbyADMIN(Request $request)
    {
        $validate = $request->validate([
            'program_id' => 'required|exists:program_diklat_eksternal,id',
            'nama_karyawan' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date|after_or_equal:today',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_diklat' => 'required|integer',
            'penyelenggara' => 'nullable|string|max:255',
            'nrp' => 'nullable|string|max:255',
            'bukti_hadir' => 'required|file|mimes:pdf,jpg,jpeg|max:2048',
        ], [
            // Opsional: Kustomisasi pesan error bahasa Indonesia agar lebih user-friendly
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        if ($validate['jam_diklat'] > 9) {
            return redirect()->back()->with('error', 'Jam diklat per hari tidak boleh lebih dari 10 jam.');
        }

        // Default approved karena admin yang input
        $validate['status'] = 'approved';

        $tanggalMulai = Carbon::parse($validate['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validate['tanggal_selesai']);

        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        $validate['jam_diklat'] = $validate['jam_diklat'] * $selisihHari;

        // Proses Upload Dokumen jika file valid
        if ($request->hasFile('bukti_hadir')) {
            $file = $request->file('bukti_hadir');

            // Membuat nama file unik: nrp_timestamp.ekstensi (contoh: 005191201_168456789.pdf)
            $namaFile = ($validate['nrp'] ?? 'karyawan') . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Simpan file ke dalam folder 'public/diklat_eksternal'
            $path = $file->storeAs('diklat_eksternal_', $namaFile, 'public');

            // Simpan path/nama file ke array validate untuk dimasukkan ke database
            $validate['bukti_hadir'] = $path;
        }

        $eksternal = DiklatEksternal::create($validate);
        $this->updateRekapBulanan(
            $eksternal->nrp,
            date('Y', strtotime($eksternal->tanggal_mulai)),
            date('n', strtotime($eksternal->tanggal_mulai))
        );

        // Panggil rekap
        // $this->updateRekapBulanan(
        //     $eksternal->nrp,
        //     date('Y', strtotime($eksternal->tanggal_mulai)),
        //     date('n', strtotime($eksternal->tanggal_mulai))
        // );
        return redirect()->route('Diklat.eksternal');
    }

    public function updateProgrambyADMIN(Request $request, $id)
    {
        $validate = $request->validate([
            'nama_diklat' => 'required|string|max:255',
            'tahun' => 'required|string|max:255',
        ]);
        
        ProgramEksternal::findOrFail($id)->update($validate);
        
        // Return bisa disesuaikan dengan rute Admin kamu
        return redirect()->back()->with('success', 'Program berhasil diperbarui oleh Admin');
    }

    public function updateDetailbyADMIN(Request $request, $id)
    {
        $diklat = DiklatEksternal::findOrFail($id);

        $validate = $request->validate([
            'program_id' => 'required|exists:program_diklat_eksternal,id',
            'nama_karyawan' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date', // Admin mungkin butuh mengedit tanggal di masa lalu, jadi after_or_equal:today bisa dihilangkan untuk admin
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_diklat' => 'required|integer',
            'penyelenggara' => 'nullable|string|max:255',
            'nrp' => 'nullable|string|max:255',
            'bukti_hadir' => 'nullable|file|mimes:pdf,jpg,jpeg|max:2048',
        ]);

        // Karena ini Admin, pastikan status tetap/menjadi approved
        $validate['status'] = 'approved';

        $tanggalMulai = Carbon::parse($validate['tanggal_mulai']);
        $tanggalSelesai = Carbon::parse($validate['tanggal_selesai']);
        $selisihHari = $tanggalMulai->diffInDays($tanggalSelesai) + 1;
        
        $validate['jam_diklat'] = $validate['jam_diklat'] * $selisihHari;

        if ($request->hasFile('dokumen')) {
            // Hapus file lama jika ada
            if ($diklat->dokumen && Storage::disk('public')->exists($diklat->dokumen)) {
                Storage::disk('public')->delete($diklat->dokumen);
            }

            $file = $request->file('dokumen');
            $namaFile = ($validate['nrp'] ?? 'karyawan') . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs('diklat_eksternal_', $namaFile, 'public');
            $validate['dokumen'] = $path;
        }

        $diklat->update($validate);

        $this->updateRekapBulanan(
            $diklat->nrp,
            date('Y', strtotime($diklat->tanggal_mulai)),
            date('n', strtotime($diklat->tanggal_mulai))
        );

        return redirect()->back()->with('success', 'Data detail diklat berhasil diperbarui dan disetujui');
    }

    public function destroyDetailbyADMIN($id)
    {
        $eksternal = DiklatEksternal::findOrFail($id);
        
        // Hapus file dokumen jika ada
        if ($eksternal->dokumen && Storage::disk('public')->exists($eksternal->dokumen)) {
            Storage::disk('public')->delete($eksternal->dokumen);
        }

        // Hapus bukti hadir jika ada
        if ($eksternal->bukti_hadir && Storage::disk('public')->exists($eksternal->bukti_hadir)) {
            Storage::disk('public')->delete($eksternal->bukti_hadir);
        }

        $nrp = $eksternal->nrp;
        $tahun = date('Y', strtotime($eksternal->tanggal_mulai));
        $bulan = date('n', strtotime($eksternal->tanggal_mulai));

        $eksternal->delete();

        // KARENA DATA DIHAPUS: Update rekap agar jamnya berkurang
        $this->updateRekapBulanan($nrp, $tahun, $bulan);

        return redirect()->back()->with('success', 'Detail diklat eksternal berhasil dihapus');
    }

    public function destroyProgrambyADMIN($id)
    {
        $program = ProgramEksternal::findOrFail($id);
        // Hati-hati: Pastikan apakah menghapus program juga perlu menghapus detail dan me-rekap ulang? 
        // Kalau iya, logikanya harus ditambahkan. Kalau tidak (cascade on delete di database), cukup delete saja.
        $program->delete();
        
        return redirect()->back()->with('success', 'Program diklat eksternal berhasil dihapus');
    }

}
