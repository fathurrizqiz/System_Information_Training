<?php

namespace App\Console\Commands;

use App\Models\EvaluasiDetailInternal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecalculateSentiments extends Command
{
    protected $signature = 'sentiment:recalculate';
    protected $description = 'Menghitung ulang sentimen evaluasi yang belum diproses';

    public function handle()
    {
        $this->info('Mencari data yang belum memiliki sentimen...');

        $evaluasis = EvaluasiDetailInternal::whereNull('sentimen_materi')->get();

        if ($evaluasis->isEmpty()) {
            $this->info('Tidak ada data yang perlu diproses.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$evaluasis->count()} data.");

        foreach ($evaluasis as $index => $evaluasi) {

            $this->line(
                sprintf(
                    '[%d/%d] Memproses ID %d',
                    $index + 1,
                    $evaluasis->count(),
                    $evaluasi->id
                )
            );

            $result = $this->analyzeSentiment($evaluasi->evaluasimateri);

            if ($result) {

                $evaluasi->update([
                    'sentimen_materi' => $result['label'] ?? null,
                ]);

                $this->info("✔ Sentimen: {$result['label']}");

            } else {

                $this->warn('✖ Gagal menganalisis.');

            }

            // Opsional, jika AI cukup berat
            sleep(1);
        }

        $this->info('Selesai.');

        return Command::SUCCESS;
    }

    private function analyzeSentiment($komentar = null)
    {
        if (empty(trim((string) $komentar))) {
            return null;
        }

        try {

            $response = Http::timeout(180)
                ->post(env('AI_SERVICE_URL') . '/predict', [
                    'komentar' => $komentar,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Throwable $e) {

            Log::error('AI Connection Error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}