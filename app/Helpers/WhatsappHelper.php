<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Tambahkan ini
use App\Models\WaTemplate;

class WhatsappHelper
{
    public static function send($target, $templateSlug, $placeholders = [])
    {
        // Debug 1: Cek apakah fungsi terpanggil
        Log::info("Mencoba kirim WA ke: $target dengan template: $templateSlug");

        $template = WaTemplate::where('slug', $templateSlug)->first();

        if (!$template) {
            Log::error("Template tidak ditemukan: $templateSlug");
            return ['status' => false, 'reason' => 'Template not found'];
        }

        $pesan = $template->pesan;
        foreach ($placeholders as $key => $value) {
            $pesan = str_replace('{' . $key . '}', $value, $pesan);
        }

        // Debug 2: Cek isi pesan akhir
        Log::info("Isi pesan: " . $pesan);

        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $pesan,
            ]);

            $result = $response->json();
            
            // Debug 3: Cek respon dari Fonnte
            Log::info("Respon Fonnte:", $result);

            return $result;
        } catch (\Exception $e) {
            Log::error("Koneksi API Gagal: " . $e->getMessage());
            return ['status' => false, 'reason' => 'Connection error'];
        }
    }
}