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
        $evaluasi1 = DiklatKaryawan::all()->map(function ($item) {

            $comments = [
                [
                    'text' => $item->evaluasimateri,
                    'aspect' => 'materi'
                ],
                [
                    'text' => $item->evaluasipengajar,
                    'aspect' => 'pemateri'
                ]
            ];

            $positive = 0;
            $neutral = 0;
            $negative = 0;

            foreach ($comments as $c) {
                if (!$c['text'])
                    continue;

                $result = $this->analyzeSentiment(
                    $item->evaluasimateri,
                    $item->evaluasipengajar
                );

                if ($result) {
                    if (($result['materi']['label'] ?? null) === 'positive') {
                        $positive++;
                    } elseif (($result['materi']['label'] ?? null) === 'neutral') {
                        $neutral++;
                    } elseif (($result['materi']['label'] ?? null) === 'negative') {
                        $negative++;
                    }

                    if (($result['pemateri']['label'] ?? null) === 'positive') {
                        $positive++;
                    } elseif (($result['pemateri']['label'] ?? null) === 'neutral') {
                        $neutral++;
                    } elseif (($result['pemateri']['label'] ?? null) === 'negative') {
                        $negative++;
                    }
                }

                $item->sentiment = [
                    'positive' => $positive,
                    'neutral' => $neutral,
                    'negative' => $negative,
                ];
                // dd($result);

                return $item;
            }
        });

        $evaluasi2 = PendidikanFormalModels::with([
            'details' => function ($q) {
                $q->select('id', 'program_id', 'nama_diklat')
                    ->with([
                        'evaluasi' => function ($q2) {
                            $q2->select('id', 'detail_id', 'evaluasipengajar', 'evaluasimateri');
                        }
                    ]);
            }
        ])
            ->select('id', 'nama_program')
            ->get()
            ->map(function ($program) {
                $program->details->map(
                    function ($detail) {


                        $detail->sentiment = [
                            'positive' => rand(5, 15),

                            'negative' => rand(1, 5),
                            'neutral' => rand(1, 5)

                        ];
                        return $detail;
                    }

                );

                return $program;
            });

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

        // 1. Inisialisasi penghitung terpisah
        $counts = [
            'materi' => ['positive' => 0, 'neutral' => 0, 'negative' => 0],
            'pemateri' => ['positive' => 0, 'neutral' => 0, 'negative' => 0],
        ];

        $comments = [];

        foreach ($evaluasis as $ev) {
            // Panggil analyzeSentiment (asumsi mengembalikan array sentiment untuk kedua aspek)
            $res = $this->analyzeSentiment($ev->evaluasimateri, $ev->evaluasipengajar);

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

            // 4. Simpan Komentar untuk ditampilkan di list
            if (!empty($ev->evaluasimateri)) {
                $comments[] = [
                    'text' => $ev->evaluasimateri,
                    'aspect' => 'materi',
                    'sentiment' => $this->mapSentiment($materiLabel)
                ];
            }
            if (!empty($ev->evaluasipengajar)) {
                $comments[] = [
                    'text' => $ev->evaluasipengajar,
                    'aspect' => 'pemateri',
                    'sentiment' => $this->mapSentiment($pemateriLabel)
                ];
            }
        }

        return Inertia::render('Evaluasi/detail', [
            'detail' => $detail,
            'comments' => $comments,
            'sentiment' => $counts // Sekarang datanya dinamis!
        ]);
    }

    // Mapping 
    function mapSentiment($label)
    {
        return match ($label) {
            'positive' => 'positive',
            'neutral' => 'neutral',
            'negative' => 'negative',
            default => 'negative',
        };
    }

    // Helper untuk AI
    private function analyzeSentiment($materi = null, $pemateri = null)
    {
        try {
            $response = Http::timeout(1000)->post('http://127.0.0.1:5000/predict', [
                'materi' => $materi,
                'pemateri' => $pemateri
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('AI Error: ' . $e->getMessage());
            return null;
        }
    }
}
