<?php

namespace App\Jobs;

use App\Helpers\WhatsappHelper;
use App\Models\WaLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target;
    protected $nama;
    protected $slug;
    protected $data;

    public function __construct($target, $nama, $slug, $data)
    {
        $this->target = $target;
        $this->nama = $nama;
        $this->slug = $slug;
        $this->data = $data;
    }

    public function handle()
    {
        \Log::info("Memulai Job WA untuk: " . $this->target);

        try {
            // Ambil template langsung di sini
            $template = \App\Models\WaTemplate::where('slug', $this->slug)->first();

            if (!$template) {
                \Log::error("Job Gagal: Template {$this->slug} tidak ada.");
                return;
            }

            // Susun pesan
            $pesanFinal = str_replace(
                ['{nama}', '{judul}', '{tanggal}', '{lokasi}'],
                [
                    $this->data['nama'] ?? '',
                    $this->data['judul'] ?? '',
                    $this->data['tanggal'] ?? '',
                    $this->data['lokasi'] ?? ''
                ],
                $template->pesan
            );

            // Kirim pakai Http langsung (tanpa helper dulu untuk tes)
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->asForm()
                ->withHeaders(['Authorization' => env('FONNTE_TOKEN')])
                ->post('https://api.fonnte.com/send', [
                    'target' => $this->target,
                    'message' => $pesanFinal,
                ]);

            // Simpan Log
            \App\Models\WaLog::create([
                'nomor_tujuan' => $this->target,
                'nama_penerima' => $this->nama,
                'pesan' => $pesanFinal,
                'status' => $response->successful() ? 'success' : 'failed',
                'response_api' => $response->body(),
            ]);

            \Log::info("Job Selesai untuk: " . $this->target);

        } catch (\Exception $e) {
            \Log::error("Error di Job WA: " . $e->getMessage());
        }
    }
}