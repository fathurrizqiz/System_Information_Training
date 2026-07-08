<?php

namespace App\Jobs;

use App\Models\PeriodeBagianDetailInternal;
use App\Models\TemplatePembahasanSertifikat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class GenerateCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public int $pesertaId)
    {
    }

    public function handle()
    {
        Log::info('[CERT] Job started', [
            'peserta_id' => $this->pesertaId,
            'time' => now()->toDateTimeString(),
        ]);

        DB::transaction(function () {

            $peserta = PeriodeBagianDetailInternal::lockForUpdate()
                ->find($this->pesertaId);

            if (!$peserta) {
                Log::error('[CERT] Peserta NOT FOUND', [
                    'peserta_id' => $this->pesertaId,
                ]);
                return;
            }

            Log::info('[CERT] Peserta found', [
                'id' => $peserta->id,
                'nrp' => $peserta->nrp,
                'pree_done_at' => $peserta->pree_done_at,
                'post_done_at' => $peserta->post_done_at,
                'sertifikat_generated_at' => $peserta->sertifikat_generated_at,
            ]);

            if (!$peserta->pree_done_at || !$peserta->post_done_at) {
                Log::warning('[CERT] PRE / POST belum lengkap', [
                    'pree' => $peserta->pree_done_at,
                    'post' => $peserta->post_done_at,
                ]);
                return;
            }

            if ($peserta->sertifikat_generated_at) {
                Log::warning('[CERT] Sertifikat sudah pernah dibuat', [
                    'sertifikat_generated_at' => $peserta->sertifikat_generated_at,
                ]);
                return;
            }

            Log::info('[CERT] Ambil materi sertifikat');

            $materi = TemplatePembahasanSertifikat::where('periode_id', $peserta->periode_id)
                ->orderBy('id')
                ->get();

            Log::info('[CERT] Materi count', [
                'total' => $materi->count(),
            ]);

            try {
                Log::info('[CERT] Generate PDF mulai');

                $pdf = Pdf::loadView('sertifikat.template', [
                    'nama' => $peserta->nama_karyawan,
                    'nama_diklat' => $peserta->periode->detail->nama_diklat,
                    'tanggal' => now()->format('d F Y'),
                    'direktur' => 'Nama Direktur',
                    'materi' => $materi->values()->map(fn($m, $i) => [
                        'no' => $i + 1,
                        'materi' => $m->materi,
                    ])->toArray(),
                ])->setPaper('A4', 'landscape');

                $path = 'sertifikat/' . $peserta->nrp . '_' . $peserta->id . '.pdf';

                Log::info('[CERT] Simpan PDF', [
                    'path' => $path,
                ]);

                Storage::disk('public')->put($path, $pdf->output());

                Log::info('[CERT] PDF berhasil disimpan');

                $peserta->update([
                    'sertifikat_path' => $path,
                    'sertifikat_generated_at' => now(),
                ]);

                Log::info('[CERT] Database updated', [
                    'sertifikat_path' => $path,
                ]);

            } catch (\Throwable $e) {
                Log::error('[CERT] ERROR saat generate PDF', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e; // BIAR JOB FAILED (penting!)
            }
        });
    }

}

