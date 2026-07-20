<?php

namespace App\Console\Commands;

use App\Models\EvaluasiDetailInternal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecalculateSentiments extends Command
{

    protected $signature = 'sentiment:recalculate';
    protected $description = 'Menghitung ulang sentimen untuk data evaluasi lama yang belum diproses';

    public function handle()
    {
        $this->info('Mencari data evaluasi yang belum memiliki hasil sentimen...');

        // Hanya ambil data yang teksnya ada tapi sentimennya masih kosong
        $evaluasis = EvaluasiDetailInternal::whereNull('sentimen_materi')
            ->orWhereNull('sentimen_pengajar')
            ->get();

        $total = $evaluasis->count();

        if ($total === 0) {
            $this->info('Semua data sudah memiliki hasil sentimen. Tidak ada yang perlu diproses.');
            return;
        }

        $this->info("Ditemukan {$total} data. Memulai proses...");
        $count = 0;

        foreach ($evaluasis as $ev) {
            $count++;
            $this->line("Memproses data ke-{$count}/{$total} (ID: {$ev->id})");

            // Panggil fungsi analisis (sama seperti di controller Anda)
            $result = $this->analyzeSentiment($ev->evaluasimateri, $ev->evaluasipengajar);

            if ($result) {
                $ev->update([
                    'sentimen_materi' => $result['materi']['label'] ?? null,
                    'sentimen_pengajar' => $result['pemateri']['label'] ?? null,
                ]);
                $this->info("  -> Berhasil update: Materi({$result['materi']['label']}), Pengajar({$result['pemateri']['label']})");
            } else {
                $this->warn("  -> Gagal menganalisis atau teks kosong.");
            }

            // PENTING: Beri jeda 1 detik agar tidak membebani server AI
            sleep(1);
        }

        $this->info('Proses selesai! Silakan cek halaman Evaluasi.');
    }

    // Salinan fungsi analyzeSentiment dari Controller Anda
    private function analyzeSentiment($materi = null, $pemateri = null)
    {
        if (empty($materi) && empty($pemateri)) {
            return null;
        }

        try {
            $hfSpaceUrl = env('AI_SERVICE_URL', 'https://vampire123456-indobert-api-service.hf.space');

            $response = Http::timeout(30)
                ->withOptions(['verify' => false])
                ->post($hfSpaceUrl . '/predict', [
                    'materi' => $materi,
                    'pemateri' => $pemateri
                ]);

            if ($response->failed()) {
                Log::error('AI Service Failed', ['status' => $response->status()]);
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('AI Connection Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

}
