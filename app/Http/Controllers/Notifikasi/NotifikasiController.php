<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\HLCManajement;
use App\Models\NoHpKaryawan;
use App\Models\PeriodeBagianDetailInternal;
use App\Models\PeriodeUtama;
use App\Models\WaTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifikasiController extends Controller
{
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
                    $jadwal->diklat ?? 'Diklat',
                    $tanggal,
                    $jadwal->tempat ?? 'Buka Aplikasi untuk melihat informasi diklat'
                ],
                $template->pesan
            );

            $htmlContent = view('email.notifikasi_diklat', [
                'karyawan' => $karyawan,
                'jadwal' => $jadwal->tempat ?? 'Buka Aplikasi untuk melihat informasi diklat',
                'tanggal' => $tanggal,
                'pesanTemplate' => $pesanFinal,
            ])->render();

        

            // Log::info("Isi Pesan:");
            // Log::info($pesanFinal);

            try {

                $response = Http::withoutVerifying()->withToken(env('RESEND_TOKEN'))
                    ->post('https://api.resend.com/emails', [
                        'from' => 'Sistem Diklat <noreply@eichar-diklat.my.id>',
                        'to' => [$karyawan->email],
                        'subject' => 'Notifikasi Diklat',
                        'html' => $htmlContent,
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


        $idJadwal = $request->id;
        $slugDicari = $request->template_slug;

        $jadwal = DiklatEksternal::select('id', 'tanggal_mulai', 'diklat', 'nrp')->findOrFail($idJadwal);

        $daftarNrp = [$jadwal->nrp];

        $penerima = NoHpKaryawan::whereIn('nrp', $daftarNrp)->get();

        if ($penerima->isEmpty()) {
            Log::warning("Nomor WA / Email tidak ditemukan");
            return back()->with('error', 'Tidak ada data penerima.');
        }

        $template = WaTemplate::where('slug', $slugDicari)->first();

        if (!$template) {
            Log::error("Template {$slugDicari} tidak ditemukan");
            return back()->with('error', 'Template tidak ditemukan.');
        }

        $tanggal = Carbon::parse($jadwal->tanggal_mulai)->translatedFormat('d F Y');

        foreach ($penerima as $karyawan) {

            if (empty($karyawan->email)) {
                Log::warning("Karyawan {$karyawan->nama} tidak memiliki email.");
                continue;
            }


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

            $htmlContent = view('email.notifikasi_diklat', [
                'karyawan' => $karyawan,
                'jadwal' => $jadwal->tempat ?? 'Buka Aplikasi untuk melihat informasi diklat',
                'tanggal' => $tanggal,
                'pesanTemplate' => $pesanFinal,
            ])->render();

            try {
                $response = Http::withoutVerifying()->withToken(env('RESEND_TOKEN'))
                    ->post('https://api.resend.com/emails', [
                        'from' => 'Sistem Diklat <noreply@eichar-diklat.my.id>',
                        'to' => [$karyawan->email],
                        'subject' => 'Notifikasi Diklat - ' . ($jadwal->diklat ?? 'Informasi'),
                        'html' => $htmlContent, // Gunakan HTML dari render view
                    ]);

                // WaLog::create([
                //     'nomor_tujuan'  => $karyawan->nomor_wa,
                //     'nama_penerima' => $karyawan->nama,
                //     'pesan'         => $pesanFinal,
                //     'status'        => $response->successful() ? 'success' : 'failed',
                //     'response_api'  => $response->body(),
                // ]);

            } catch (\Throwable $e) {
                Log::error("EXCEPTION: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Email notifikasi berhasil dikirim.');
    }
}
