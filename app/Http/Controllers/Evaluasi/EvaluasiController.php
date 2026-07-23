<?php

namespace App\Http\Controllers\Evaluasi;

use App\Http\Controllers\Controller;
use App\Models\DetailInternal;
use App\Models\DiklatKaryawan;
use App\Models\EvaluasiDetailInternal;
use App\Models\PendidikanFormalModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class EvaluasiController extends Controller
{
    public function index()
    {
        // 1. Evaluasi Karyawan (Diklat Eksternal)
        // Catatan: Pastikan tabel diklat_karyawans sudah punya kolom sentimen_materi/pengajar 
        // agar bisa menggunakan cara yang ringan seperti Internal.
        $evaluasi1 = DiklatKaryawan::all()->map(function ($item) {
            // Jika belum pakai kolom sentimen di DB, logic AI lama tetap di sini.
            // Jika sudah pakai kolom sentimen, gunakan accessor di model DiklatKaryawan juga.
            return $item;
        });

        // 2. Evaluasi Internal (Pendidikan Formal)
        // PERBAIKAN: Gunakan PendidikanFormalModels sebagai root, bukan DetailInternal
        $evaluasi2 = PendidikanFormalModels::with([
            'details' => function ($q) {
                // PERBAIKAN: Gunakan 'evaluasis' (jamak) sesuai nama function di model DetailInternal
                $q->with('evaluasi:id,detail_id,sentimen_materi,sentimen_pengajar');

                // Opsional: Pilih kolom detail yang diperlukan saja agar lebih ringan
                $q->select('id', 'program_id', 'nama_diklat');
            }
        ])
            ->select('id', 'nama_program') // Pilih kolom program yang diperlukan
            ->get();

        return Inertia::render('Evaluasi/evaluasi', [
            'evaluasiKaryawan' => $evaluasi1,
            'evaluasiInternal' => $evaluasi2
        ]);
    }
    public function show($id)
{
    set_time_limit(0);
    $detail = DetailInternal::with('evaluasi')->findOrFail($id);
    $evaluasis = $detail->evaluasi;

    $counts = [
        'materi'   => ['positive' => 0, 'neutral' => 0, 'negative' => 0],
        'pemateri' => ['positive' => 0, 'neutral' => 0, 'negative' => 0],
    ];

    $comments = [];

    foreach ($evaluasis as $ev) {
        // 1 komentar bebas -> API deteksi aspek otomatis + sentimen per aspek
        $res = $this->analyzeSentiment($ev->evaluasimateri);

        if (!$res)
            continue;

        // 2. Hitung Sentiment Materi
        $materiLabel = $res['materi']['label'] ?? null;
        if ($materiLabel === 'positive')
            $counts['materi']['positive']++;
        elseif ($materiLabel === 'neutral')
            $counts['materi']['neutral']++;
        elseif ($materiLabel === 'negative')
            $counts['materi']['negative']++;

        // 3. Hitung Sentiment Pemateri
        $pemateriLabel = $res['pemateri']['label'] ?? null;
        if ($pemateriLabel === 'positive')
            $counts['pemateri']['positive']++;
        elseif ($pemateriLabel === 'neutral')
            $counts['pemateri']['neutral']++;
        elseif ($pemateriLabel === 'negative')
            $counts['pemateri']['negative']++;

        // 4. Simpan komentar untuk list -> 1 baris per aspek yang TERDETEKSI
        //    (kalau materi & pemateri sama-sama terdeteksi, komentar ini
        //     akan muncul 2x di list, masing-masing dengan aspek berbeda)
        if (!empty($ev->evaluasimateri)) {
            if ($materiLabel) {
                $comments[] = [
                    'text' => $ev->evaluasimateri,
                    'aspect' => 'materi',
                    'sentiment' => $this->mapSentiment($materiLabel)
                ];
            }
            if ($pemateriLabel) {
                $comments[] = [
                    'text' => $ev->evaluasimateri,
                    'aspect' => 'pemateri',
                    'sentiment' => $this->mapSentiment($pemateriLabel)
                ];
            }
        }
    }

    return Inertia::render('Evaluasi/detail', [
        'detail' => $detail,
        'comments' => $comments,
        'sentiment' => $counts
    ]);
}

function mapSentiment($label)
{
    return match ($label) {
        'positive' => 'positive',
        'neutral' => 'neutral',
        'negative' => 'negative',
        default => 'negative',
    };
}

// Helper untuk AI — sekarang kirim 1 field "komentar" saja
private function analyzeSentiment($komentar = null)
{
    if (empty(trim((string) $komentar))) {
        return null;
    }

    try {

        $url = rtrim(env('AI_SERVICE_URL'), '/') . '/predict';

        $response = Http::timeout(180)
            ->withoutVerifying() // opsional, hanya jika ada masalah SSL
            ->post($url, [
                'komentar' => $komentar,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        \Log::error('AI Error', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;

    } catch (\Throwable $e) {

        \Log::error('AI Exception', [
            'url' => $url ?? null,
            'message' => $e->getMessage(),
        ]);

        return null;
    }
}

    // // AI Hunging Face
    // private function analyzeSentiment($materi = null, $pemateri = null)
    // {
    //     // Jika keduanya kosong, tidak perlu call API
    //     if (empty($materi) && empty($pemateri)) {
    //         return null;
    //     }

    //     try {
    //         $hfSpaceUrl = env('AI_SERVICE_URL', 'https://vampire123456-indobert-api-service.hf.space');

    //         // Timeout 30 detik.
    //         $response = Http::timeout(30)
    //             ->withOptions([
    //                 'verify' => false //SSL sementara
    //             ])
    //             ->post($hfSpaceUrl . '/predict', [
    //                 'materi' => $materi,
    //                 'pemateri' => $pemateri
    //             ]);

    //         if ($response->failed()) {
    //             Log::error('AI Service Failed', ['status' => $response->status(), 'body' => $response->body()]);
    //             return null; 
    //         }

    //         return $response->json();

    //     } catch (\Exception $e) {
    //         Log::error('AI Connection Error', ['message' => $e->getMessage()]);
    //         return null;
    //     }
    // }

}
