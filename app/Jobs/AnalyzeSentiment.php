<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnalyzeSentiment implements ShouldQueue
{
    use Queueable;

    protected $evaluasi;

    public function __construct($evaluasi)
    {
        $this->evaluasi = $evaluasi;
    }

    public function handle()
    {
        $text = $this->evaluasi->evaluasimateri . ' ' . $this->evaluasi->evaluasipengajar;

        // panggil python
        $result = shell_exec("python3 ai/sentiment.py " . escapeshellarg($text));

        $result = json_decode($result, true);

        $this->evaluasi->update([
            'sentiment_label' => $result['label'] ?? null,
            'sentiment_score' => $result['score'] ?? null,
        ]);
    }

}
