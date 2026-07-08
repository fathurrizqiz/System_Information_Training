<?php

namespace App\Http\Controllers\RencanaDiklat\RPT;

use App\Http\Controllers\Controller;
use App\Models\AksiDetailInternal;
use App\Models\Karyawans;
use App\Models\PeriodeBagianDetailInternal;
use App\Models\UserAnswerPostPreeDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Jobs\GenerateCertificateJob;

class PresensiDetailController extends Controller
{
    public function hadir(Request $request)
    {
        $request->validate([
            'nrp' => 'required',
            'detail_id' => 'required|integer',
        ]);

        $peserta = PeriodeBagianDetailInternal::where('nrp', $request->nrp)
            ->where('detail_program_id', $request->detail_id)
            ->firstOrFail();

        if ($peserta->hadir_at) {
            return back()->with('info', 'Anda sudah tercatat hadir');
        }

        $peserta->update([
            'hadir_at' => now(),
        ]);

        return back()->with('success', 'Presensi berhasil');
    }


    public function index($periode_id)
    {
        // Ambil data periode dan jam_diklat
        $aksi = AksiDetailInternal::with('periodeUtama')
            ->where('periode_id', $periode_id)
            ->firstOrFail();

        $detail_id = $aksi->periodeUtama->detail_id;

        // Ambil SEMUA jawaban untuk diklat ini
        $answers = UserAnswerPostPreeDetail::with('question.test')
            ->whereHas(
                'question.test',
                fn($q) =>
                $q->where('detail_program_id', $detail_id)
            )->get();

        // Ekstrak NRP unik yang sudah mengisi
        $nrpsWithAnswers = $answers->pluck('nrp')->unique()->toArray();

        if (empty($nrpsWithAnswers)) {
            // Tidak ada yang mengisi
            return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/Presensi/index', [
                'karyawan' => [],
                'jam_diklat' => $aksi->jam_diklat,
                'tanggal' => $aksi->periodeUtama->tanggal ?? '-',
            ]);
        }

        // Ambil data karyawan yang NRP-nya ada di jawaban
        $karyawanMap = Karyawans::whereIn('nrp', $nrpsWithAnswers)
            ->pluck('nama_karyawan', 'nrp'); // [ 'NRP123' => 'Joko', ... ]

        // Hitung nilai per NRP
        $scoreMap = [];
        foreach ($answers as $ans) {
            $nrp = $ans->nrp;
            $type = optional($ans->question->test)->type;
            if (!isset($scoreMap[$nrp])) {
                $scoreMap[$nrp] = ['pree' => 0, 'post' => 0];
            }
            if ($ans->is_correct && in_array($type, ['pree', 'post'])) {
                $scoreMap[$nrp][$type] += $ans->question->bobot ?? 0;
            }
        }

        // Gabungkan jadi array untuk kirim ke Vue
        $karyawanDenganNilai = [];

        foreach ($nrpsWithAnswers as $nrp) {
            $karyawanDenganNilai[] = [
                'nrp' => $nrp,
                'nama_karyawan' => $karyawanMap[$nrp] ?? 'Nama tidak ditemukan',
                'pre_score' => round($scoreMap[$nrp]['pree'] ?? 0, 2),
                'post_score' => round($scoreMap[$nrp]['post'] ?? 0, 2),
            ];
        }

        return Inertia::render('RencanaDiklat/RPT/PendidikanFormal/Presensi/index', [
            'karyawan' => $karyawanDenganNilai,
            'jam_diklat' => $aksi->jam_diklat,
            'tanggal' => $aksi->periodeUtama->tanggal ?? '-',
        ]);
    }

    protected function checkAndGenerateCertificate($peserta)
    {
        if (!$peserta->hadir_at) {
            return;
        }

        if (!$peserta->post_done_at || !$peserta->pree_done_at) {
            return;
        }

        if ($peserta->sertifikat_generated_at) {
            return;
        }

        // 🚀 lempar ke Job
        GenerateCertificateJob::dispatch($peserta->id);
    }

}
