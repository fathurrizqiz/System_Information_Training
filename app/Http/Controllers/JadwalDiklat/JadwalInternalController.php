<?php

namespace App\Http\Controllers\JadwalDiklat;

use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappJob;
use App\Models\DiklatEksternal;
use App\Models\HLCManajement;
use App\Models\InternalMeetModels;
use App\Models\NoHpKaryawan;
use App\Models\PeriodeBagianDetailInternal;
use App\Models\PeriodeUtama;
use App\Models\ProgramEksternal;
use App\Models\ProgramHlc;
use App\Models\WaTemplate;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class JadwalInternalController extends Controller
{

    public function index(Request $request)
    {
        $nrp = Auth::user()->nrp;
        $search = $request->input('search');

        $internal = PeriodeUtama::with(['detail', 'meeting'])
            ->when(auth()->user()->role == 'admin_diklat', function ($query) {
                $query->whereHas('peserta', function ($peserta) {
                    $peserta->where('nrp', auth()->user()->nrp);
                });
            })
            ->where('tanggal', '>=', Carbon::today())
            ->when($search, function ($query) use ($search) {
                $query->whereHas('detail', function ($detail) use ($search) {
                    $detail->where('nama_diklat', 'ILIKE', "%{$search}%");
                });
            })
            ->orderBy('tanggal')
            ->get();

        // 2. HLC (Status Offered/Undangan)
        $hlc = ProgramHlc::whereHas('hlc', function ($q) use ($nrp) {
            if (auth()->user()->role == 'admin_diklat') {
                $q->where('nrp', $nrp);
            }
            $q

                ->whereIn('status', [
                    'Setuju',
                    'Hadir',
                    'approved',
                    'rejected'
                ])
                ->whereDate('tanggal_selesai', '>=', Carbon::today());
        })
            ->with([
                'hlc' => function ($q) use ($nrp) {
                    if (auth()->user()->role == 'admin_diklat') {
                        $q->where('nrp', $nrp);
                    }
                    $q
                        ->whereIn('status', [
                            'Setuju',
                            'Hadir',
                            'approved',
                            'rejected'
                        ])
                        ->whereDate('tanggal_selesai', '>=', Carbon::today())
                        ->with([
                            'kehadiranHariIni'
                        ])
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) =>
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();

        // pengecekan
        $hlc->each(function ($program) use ($nrp) {
            $program->hlc->each(function ($hlc) use ($nrp) {
                $hlc->is_peserta = $hlc->nrp === $nrp;
            });
        });

        // 3. Eksternal (Status Offered/Undangan)
        $eksternal = ProgramEksternal::whereHas('eksternal', function ($q) use ($nrp) {
            if (auth()->user()->role == 'admin_diklat') {
                $q->where('nrp', $nrp);
            }
            $q
                ->whereIn('status', [
                    'Setuju',
                    'Hadir',
                    'approved',
                    'rejected'
                ])
                ->whereDate('tanggal_selesai', '>=', Carbon::today());
        })
            ->with([
                'eksternal' => function ($q) use ($nrp) {
                    if (auth()->user()->role == 'admin_diklat') {
                        $q->where('nrp', $nrp);
                    }
                    $q

                        ->whereIn('status', [
                            'Setuju',
                            'Hadir',
                            'approved',
                            'rejected'
                        ])
                        ->whereDate('tanggal_selesai', '>=', Carbon::today())
                        ->with([
                            'kehadiranHariIni'
                        ])
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) =>
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();

        // pengecekan
        $eksternal->each(function ($program) use ($nrp) {
            $program->eksternal->each(function ($eks) use ($nrp) {
                $eks->is_peserta = $eks->nrp === $nrp;
            });
        });

        $templates = WaTemplate::all(['id', 'nama_template', 'slug']);
        return Inertia::render('Jadwal/AdminInternalJadwal', [
            'jadwalInternal' => $internal,
            'jadwalHLC' => $hlc,
            'jadwalEksternal' => $eksternal,
            'filters' => ['search' => $search],
            'templates' => $templates
        ]);
    }

    // Whattsapp Notification
    // Internal
    public function sendWhatsappNotification(Request $request)
    {
        // Log::info("=== MULAI KIRIM WHATSAPP ===");

        $idJadwal = $request->id;
        $slugDicari = $request->template_slug;

        // Log::info("ID Jadwal: {$idJadwal}");
        // Log::info("Slug Template: {$slugDicari}");

        $jadwal = PeriodeUtama::with('detail')->findOrFail($idJadwal);

        $daftarNrp = PeriodeBagianDetailInternal::where('periode_id', $idJadwal)
            ->pluck('nrp')
            ->toArray();

        // Log::info("Jumlah NRP ditemukan: " . count($daftarNrp));

        if (empty($daftarNrp)) {
            Log::warning("Tidak ada peserta");
            return back()->with('error', 'Belum ada peserta.');
        }

        $penerima = NoHpKaryawan::whereIn('nrp', $daftarNrp)->get();

        // Log::info("Jumlah nomor WA: " . $penerima->count());

        if ($penerima->isEmpty()) {
            Log::warning("Nomor WA tidak ditemukan");
            return back()->with('error', 'Tidak ada nomor WA.');
        }

        $template = WaTemplate::where('slug', $slugDicari)->first();

        if (!$template) {
            Log::error("Template {$slugDicari} tidak ditemukan");
            return back()->with('error', 'Template tidak ditemukan.');
        }

        $tanggal = Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y');

        foreach ($penerima as $karyawan) {

            // Log::info("=================================");
            // Log::info("Mengirim ke {$karyawan->nama}");
            // Log::info("Nomor: {$karyawan->email}");

            $pesanFinal = str_replace(
                ['{nama}', '{judul}', '{tanggal}', '{lokasi}'],
                [
                    $karyawan->nama,
                    $jadwal->detail->nama_program ?? 'Diklat',
                    $tanggal,
                    $jadwal->tempat ?? 'Kantor'
                ],
                $template->pesan
            );

            // Log::info("Isi Pesan:");
            // Log::info($pesanFinal);

            try {

                // $response = Http::withoutVerifying()
                //     ->timeout(30)
                //     ->asForm()
                //     ->withHeaders([
                //         'Authorization' => env('FONNTE_TOKEN'),
                //     ])
                //     ->post('https://api.fonnte.com/send', [
                //         'target' => $karyawan->nomor_wa,
                //         'message' => $pesanFinal,
                //     ]);

                // Log::info("HTTP Status : " . $response->status());
                // Log::info("Response API : " . $response->body());

                $response = Http::withoutVerifying()->withToken(env('RESEND_TOKEN'))
                    ->post('https://api.resend.com/emails', [
                        'from' => 'Sistem Diklat <noreply@eichar-diklat.my.id>',
                        'to' => [$karyawan->email],
                        'subject' => 'Notifikasi Diklat',
                        'html' => nl2br($pesanFinal),
                    ]);

                // Log::info("HTTP Status : " . $response->status());
                // Log::info("Response API : " . $response->body());


                // WaLog::create([
                //     'nomor_tujuan' => $karyawan->nomor_wa,
                //     'nama_penerima' => $karyawan->nama,
                //     'pesan' => $pesanFinal,
                //     'status' => $response->successful() ? 'success' : 'failed',
                //     'response_api' => $response->body(),
                // ]);

                // Log::info("Berhasil simpan log");

            } catch (\Throwable $e) {

                // Log::error("EXCEPTION");
                // Log::error($e->getMessage());
                // Log::error($e->getFile());
                // Log::error($e->getLine());

            }
        }

        // Log::info("=== SELESAI ===");

        return back()->with('success', 'Selesai');
    }

    // HLC send Whattsapp
    public function sendWhatsappHLC(Request $request)
    {
        // Log::info("=== MULAI KIRIM WHATSAPP ===");

        $idJadwal = $request->id;
        $slugDicari = $request->template_slug;

        // Log::info("ID Jadwal: {$idJadwal}");
        // Log::info("Slug Template: {$slugDicari}");

        $jadwal = HLCManajement::select('id', 'nama_diklat', 'nrp', 'tanggal_mulai')->findOrFail($idJadwal);

        $daftarNrp = [$jadwal->nrp];


        $penerima = NoHpKaryawan::whereIn('nrp', $daftarNrp)->get();

        // Log::info("Jumlah nomor WA: " . $penerima->count());

        if ($penerima->isEmpty()) {
            Log::warning("Nomor WA tidak ditemukan");
            return back()->with('error', 'Tidak ada nomor WA.');
        }

        $template = WaTemplate::where('slug', $slugDicari)->first();

        if (!$template) {
            Log::error("Template {$slugDicari} tidak ditemukan");
            return back()->with('error', 'Template tidak ditemukan.');
        }

        $tanggal = Carbon::parse($jadwal->tanggal_mulai)->translatedFormat('d F Y');

        foreach ($penerima as $karyawan) {

            // Log::info("=================================");
            // Log::info("Mengirim ke {$karyawan->nama}");
            // Log::info("Nomor: {$karyawan->email}");

            $pesanFinal = str_replace(
                ['{nama}', '{judul}', '{tanggal}', '{lokasi}'],
                [
                    $karyawan->nama,
                    $jadwal->nama_diklat ?? 'Diklat',
                    $tanggal,
                    $jadwal->tempat ?? 'Buka Aplikasi untuk melihat informasi diklat di eichar-diklat.my.id'
                ],
                $template->pesan
            );

            // Log::info("Isi Pesan:");
            // Log::info($pesanFinal);

            try {

                $response = Http::withoutVerifying()->withToken(env('RESEND_TOKEN'))
                    ->post('https://api.resend.com/emails', [
                        'from' => 'Sistem Diklat <noreply@eichar-diklat.my.id>',
                        'to' => [$karyawan->email],
                        'subject' => 'Notifikasi Diklat',
                        'html' => nl2br($pesanFinal),
                    ]);

                // Log::info("HTTP Status : " . $response->status());
                // Log::info("Response API : " . $response->body());


                // WaLog::create([
                //     'nomor_tujuan' => $karyawan->nomor_wa,
                //     'nama_penerima' => $karyawan->nama,
                //     'pesan' => $pesanFinal,
                //     'status' => $response->successful() ? 'success' : 'failed',
                //     'response_api' => $response->body(),
                // ]);

                // Log::info("Berhasil simpan log");

            } catch (\Throwable $e) {

                // Log::error("EXCEPTION");
                // Log::error($e->getMessage());
                // Log::error($e->getFile());
                // Log::error($e->getLine());

            }
        }

        // Log::info("=== SELESAI ===");

        return back()->with('success', 'Selesai');
    }

    public function sendWhatsappEksternal(Request $request)
    {
        // Log::info("=== MULAI KIRIM WHATSAPP ===");

        $idJadwal = $request->id;
        $slugDicari = $request->template_slug;

        // Log::info("ID Jadwal: {$idJadwal}");
        // Log::info("Slug Template: {$slugDicari}");

        $jadwal = DiklatEksternal::select('id', 'tanggal_mulai', 'diklat', 'nrp')->findOrFail($idJadwal);

        // Log::info('Isi NRP di jadwal: ' . $jadwal->nrp);
        $daftarNrp = [$jadwal->nrp];


        $penerima = NoHpKaryawan::whereIn('nrp', $daftarNrp)->get();

        // Log::info("Jumlah nomor WA: " . $penerima->count());

        if ($penerima->isEmpty()) {
            Log::warning("Nomor WA tidak ditemukan");
            return back()->with('error', 'Tidak ada nomor WA.');
        }

        $template = WaTemplate::where('slug', $slugDicari)->first();

        if (!$template) {
            Log::error("Template {$slugDicari} tidak ditemukan");
            return back()->with('error', 'Template tidak ditemukan.');
        }

        $tanggal = Carbon::parse($jadwal->tanggal_mulai)->translatedFormat('d F Y');

        foreach ($penerima as $karyawan) {

            // Log::info("=================================");
            // Log::info("Mengirim ke {$karyawan->nama}");
            // Log::info("Nomor: {$karyawan->email}");

            $pesanFinal = str_replace(
                ['{nama}', '{judul}', '{tanggal}', '{lokasi}'],
                [
                    $karyawan->nama,
                    $jadwal->diklat ?? 'Diklat',
                    $tanggal,
                    $jadwal->tempat ?? 'Buka Aplikasi untuk melihat informasi diklat'
                ],
                $template->pesan
            );

            // Log::info("Isi Pesan:");
            // Log::info($pesanFinal);

            try {

                $response = Http::withoutVerifying()->withToken(env('RESEND_TOKEN'))
                    ->post('https://api.resend.com/emails', [
                        'from' => 'Sistem Diklat <noreply@eichar-diklat.my.id>',
                        'to' => [$karyawan->email],
                        'subject' => 'Notifikasi Diklat',
                        'html' => nl2br($pesanFinal),
                    ]);

                // Log::info("HTTP Status : " . $response->status());
                // Log::info("Response API : " . $response->body());

                // WaLog::create([
                //     'nomor_tujuan' => $karyawan->nomor_wa,
                //     'nama_penerima' => $karyawan->nama,
                //     'pesan' => $pesanFinal,
                //     'status' => $response->successful() ? 'success' : 'failed',
                //     'response_api' => $response->body(),
                // ]);

                // Log::info("Berhasil simpan log");

            } catch (\Throwable $e) {

                // Log::error("EXCEPTION");
                // Log::error($e->getMessage());
                // Log::error($e->getFile());
                // Log::error($e->getLine());

            }
        }

        // Log::info("=== SELESAI ===");

        return back()->with('success', 'Selesai');
    }

    public function history(Request $request)
    {
        $nrp = Auth::user()->nrp;
        $search = $request->input('search');

        // 1. Internal
        $internal = PeriodeUtama::with('detail') // Ganti detailProgram menjadi detail
            ->whereHas('peserta', fn($q) => $q->where('nrp', $nrp))
            ->where('tanggal', '<=', Carbon::today())
            ->when(
                $search,
                fn($q) =>
                // Karena nama_diklat ada di tabel detail_internal, 
                // pencarian 'where' harus diarahkan ke tabel relasinya
                $q->whereHas('detail', fn($det) => $det->where('nama_diklat', 'ILIKE', "%{$search}%"))
            )
            ->orderBy('tanggal', 'asc')
            ->get();



        // 2. HLC (Status Offered/Undangan)
        $hlc = ProgramHlc::whereHas('hlc', function ($q) use ($nrp) {
            $q->where('nrp', $nrp)
                ->whereIn('status', [

                    'approved',
                    'rejected'
                ])
                ->whereDate('tanggal_selesai', '<=', Carbon::today());
        })
            ->with([
                'hlc' => function ($q) use ($nrp) {
                    $q->where('nrp', $nrp)
                        ->whereIn('status', [

                            'approved',
                            'rejected'
                        ])
                        ->whereDate('tanggal_selesai', '<=', Carbon::today())
                        ->with([
                            'kehadiranHariIni'
                        ])
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) =>
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();

        // 3. Eksternal (Status Offered/Undangan)
        $eksternal = ProgramEksternal::whereHas('eksternal', function ($q) use ($nrp) {
            $q->where('nrp', $nrp)
                ->whereIn('status', [
                    'approved',
                    'rejected'
                ])
                ->whereDate('tanggal_selesai', '<=', Carbon::today());
        })
            ->with([
                'eksternal' => function ($q) use ($nrp) {
                    $q->where('nrp', $nrp)
                        ->whereIn('status', [

                            'approved',
                            'rejected'
                        ])
                        ->whereDate('tanggal_selesai', '<=', Carbon::today())
                        ->with([
                            'kehadiranHariIni'
                        ])
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) =>
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();
        return Inertia::render('Jadwal/History/Historyjadwal', [
            'jadwalInternal' => $internal,
            'jadwalHLC' => $hlc,
            'jadwalEksternal' => $eksternal,
            'filters' => ['search' => $search],
        ]);

    }
}
