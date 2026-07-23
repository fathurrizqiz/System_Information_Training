<?php

namespace App\Jobs;

use App\Models\EvaluasiDetailInternal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyzeSentiment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EvaluasiDetailInternal $evaluasi
    ) {
    }

    public function handle(): void
    {
        Log::info('AnalyzeSentiment START', [
            'evaluasi_id' => $this->evaluasi->id,
        ]);

        $komentar = trim((string) $this->evaluasi->evaluasimateri);

        if ($komentar === '') {
            Log::warning('Komentar kosong');
            return;
        }

        $url = rtrim(env('AI_SERVICE_URL'), '/') . '/predict';

        Log::info('AI URL', [
            'url' => $url,
        ]);

        try {

            Log::info('Mengirim request ke AI', [
                'komentar' => $komentar,
            ]);

            $response = Http::withoutVerifying()
                ->timeout(180)
                ->post($url, [
                    'komentar' => $komentar,
                ]);

            Log::info('Status Response', [
                'status' => $response->status(),
            ]);

            Log::info('Body Response', [
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                return;
            }

            $result = $response->json();

            Log::info('JSON Response', $result);

            $this->evaluasi->update([
                'sentimen_materi' => data_get($result, 'materi.label'),
                'sentimen_pengajar' => data_get($result, 'pemateri.label'),
            ]);

            Log::info('Database Updated', [
                'evaluasi_id' => $this->evaluasi->id,
            ]);

        } catch (\Throwable $e) {

            Log::error('AnalyzeSentiment Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}