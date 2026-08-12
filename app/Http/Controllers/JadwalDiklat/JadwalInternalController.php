<?php

namespace App\Http\Controllers\JadwalDiklat;

use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsappJob;
use App\Models\DiklatEksternal;
use App\Models\HLCManajement;
use App\Models\InternalMeetModels;
use App\Models\NoHpKaryawan;
use App\Models\PeriodeBagianDetailInternal;
use App\Models\PeriodeUtama;
use App\Models\ProgramEksternal;
use App\Models\ProgramHlc;
use App\Models\WaTemplate;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class JadwalInternalController extends Controller
{

    public function index(Request $request)
    {
        $nrp = Auth::user()->nrp;
        $search = $request->input('search');
        $user = auth()->user();
        $isAdminDiklat = $user->role === 'admin_diklat';

        // 1. Internal (Diperbarui agar membawa data Aksi & Token)
        $internal = PeriodeUtama::with([
                'detail', 
                'meeting', 
                'aksi', 
                'tokens',
                'peserta' => function($q) use ($user) {
                    $q->where('nrp', $user->nrp);
                }
            ])
            ->whereHas('peserta', function ($peserta) use ($user, $isAdminDiklat) {
                if (! $isAdminDiklat) {
                    $peserta->where('nrp', $user->nrp);
                }
            })
            ->whereDate('tanggal', '>=', Carbon::today())
            ->when($search, function ($query) use ($search) {
                $query->whereHas('detail', function ($detail) use ($search) {
                    $detail->where('nama_diklat', 'ILIKE', "%{$search}%");
                });
            })
            ->orderBy('tanggal')
            ->get();

        // Mapping untuk token dan status pengerjaan user
        $internal->transform(function ($periode) use ($user) {
            $tokenPree = $periode->tokens->where('type', 'pree')->first();
            $tokenPost = $periode->tokens->where('type', 'post')->first();
            $tokenEvaluasi = $periode->tokens->where('type', 'evaluasi')->first();

            // Ambil data peserta (detail internal) untuk user yang login
            $peserta = $periode->peserta->first();

            $periode->token_links = [
                'pree' => $tokenPree ? url("/test/token/pree/{$tokenPree->token}") : null,
                'post' => $tokenPost ? url("/test/token/post/{$tokenPost->token}") : null,
                'evaluasi' => $tokenEvaluasi ? url("/test/token/evaluasi/{$tokenEvaluasi->token}") : null,
            ];

            // Kirim status apakah pre-test / post-test sudah dikerjakan
            $periode->user_status = [
                'pree_done' => $peserta ? !is_null($peserta->pree_done_at) : false,
                'post_done' => $peserta ? !is_null($peserta->post_done_at) : false,
            ];

            return $periode;
        });

        // 2. HLC (Status Offered/Undangan)
        $user = auth()->user();
        $isAdminDiklat = $user->role === 'admin_diklat';
        $hlc = ProgramHlc::whereHas('hlc', function ($q) use ($user) {
            $q->when(
                $user->role !== 'admin_diklat',
                fn($query) => $query->where('nrp', $user->nrp)
            )
                ->whereIn('status', [
                    'Setuju',
                    'Hadir',
                    'approved',
                    'rejected',
                ])
                ->whereDate('tanggal_selesai', '>=', Carbon::today());
        })
            ->with([
                'hlc' => function ($q) use ($user) {
                    $q->when(
                        $user->role !== 'admin_diklat',
                        fn($query) => $query->where('nrp', $user->nrp)
                    )
                        ->whereIn('status', [
                            'Setuju',
                            'Hadir',
                            'approved',
                            'rejected',
                        ])
                        ->whereDate('tanggal_selesai', '>=', Carbon::today())
                        ->with('kehadiranHariIni')
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) => $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();

        $hlc->each(function ($program) use ($user, $isAdminDiklat) {
            $program->hlc->each(function ($hlc) use ($user, $isAdminDiklat) {
                $hlc->is_peserta = $isAdminDiklat || $hlc->nrp === $user->nrp;
            });
        });

        // 3. Eksternal (Status Offered/Undangan)
        $eksternal = ProgramEksternal::whereHas('eksternal', function ($q) use ($user) {
            $q->when(
                $user->role !== 'admin_diklat',
                fn($query) => $query->where('nrp', $user->nrp)
            )
                ->whereIn('status', [
                    'Setuju',
                    'Hadir',
                    'approved',
                    'rejected',
                ])
                ->whereDate('tanggal_selesai', '>=', Carbon::today());
        })
            ->with([
                'eksternal' => function ($q) use ($user) {
                    $q->when(
                        $user->role !== 'admin_diklat',
                        fn($query) => $query->where('nrp', $user->nrp)
                    )
                        ->whereIn('status', [
                            'Setuju',
                            'Hadir',
                            'approved',
                            'rejected',
                        ])
                        ->whereDate('tanggal_selesai', '>=', Carbon::today())
                        ->with('kehadiranHariIni')
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) => $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();

        $eksternal->each(function ($program) use ($user, $isAdminDiklat) {
            $program->eksternal->each(function ($eks) use ($user, $isAdminDiklat) {
                $eks->is_peserta = $isAdminDiklat || $eks->nrp === $user->nrp;
            });
        });

        $templates = WaTemplate::all(['id', 'nama_template', 'slug']);
        return Inertia::render('Jadwal/AdminInternalJadwal', [
            'jadwalInternal' => $internal,
            'jadwalHLC' => $hlc,
            'jadwalEksternal' => $eksternal,
            'filters' => ['search' => $search],
            'templates' => $templates
        ]);
    }


    public function history(Request $request)
    {
        $nrp = Auth::user()->nrp;
        $search = $request->input('search');

        // 1. Internal
        $internal = PeriodeUtama::with('detail') // Ganti detailProgram menjadi detail
            ->whereHas('peserta', fn($q) => $q->where('nrp', $nrp))
            ->where('tanggal', '<=', Carbon::today())
            ->when(
                $search,
                fn($q) =>
                // Karena nama_diklat ada di tabel detail_internal, 
                // pencarian 'where' harus diarahkan ke tabel relasinya
                $q->whereHas('detail', fn($det) => $det->where('nama_diklat', 'ILIKE', "%{$search}%"))
            )
            ->orderBy('tanggal', 'asc')
            ->get();



        // 2. HLC (Status Offered/Undangan)
        $hlc = ProgramHlc::whereHas('hlc', function ($q) use ($nrp) {
            $q->where('nrp', $nrp)
                ->whereIn('status', [

                    'approved',
                    'rejected'
                ])
                ->whereDate('tanggal_selesai', '<=', Carbon::today());
        })
            ->with([
                'hlc' => function ($q) use ($nrp) {
                    $q->where('nrp', $nrp)
                        ->whereIn('status', [

                            'approved',
                            'rejected'
                        ])
                        ->whereDate('tanggal_selesai', '<=', Carbon::today())
                        ->with([
                            'kehadiranHariIni'
                        ])
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) =>
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();

        // 3. Eksternal (Status Offered/Undangan)
        $eksternal = ProgramEksternal::whereHas('eksternal', function ($q) use ($nrp) {
            $q->where('nrp', $nrp)
                ->whereIn('status', [
                    'approved',
                    'rejected'
                ])
                ->whereDate('tanggal_selesai', '<=', Carbon::today());
        })
            ->with([
                'eksternal' => function ($q) use ($nrp) {
                    $q->where('nrp', $nrp)
                        ->whereIn('status', [

                            'approved',
                            'rejected'
                        ])
                        ->whereDate('tanggal_selesai', '<=', Carbon::today())
                        ->with([
                            'kehadiranHariIni'
                        ])
                        ->orderBy('tanggal_mulai', 'asc');
                }
            ])
            ->when(
                $search,
                fn($q) =>
                $q->where('nama_diklat', 'ILIKE', "%{$search}%")
            )
            ->get();
        return Inertia::render('Jadwal/History/Historyjadwal', [
            'jadwalInternal' => $internal,
            'jadwalHLC' => $hlc,
            'jadwalEksternal' => $eksternal,
            'filters' => ['search' => $search],
        ]);

    }
}
