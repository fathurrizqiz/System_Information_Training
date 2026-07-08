<?php

namespace App\Http\Middleware;

use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\HLCManajement; // Pastikan model ini ada
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');
        
        $countJadwal = 0;
        $countPersetujuan = 0;
        $countInbox = 0;

        if ($user = $request->user()) {
            $nrp = $user->nrp;
            $today = Carbon::today()->toDateString();

            // 1. PERSETUJUAN (Admin Only)
            if ($user->hasRole('admin_diklat')) {
                // Pastikan status ini sesuai dengan database Anda
                $countPersetujuan = DiklatKaryawan::where('status', 'Menunggu Persetujuan')->count();
            }

            // 2. JADWAL MENDATANG (User)
            // Menggabungkan Internal, HLC, dan Eksternal
            try {
                $countJadwal = DB::table(DB::raw("(
                    SELECT id FROM periode_bagian_detail_internal 
                    WHERE nrp = '$nrp' 
                    AND EXISTS (SELECT 1 FROM periode_detail_internal pdi WHERE pdi.id = periode_bagian_detail_internal.periode_id AND pdi.tanggal >= '$today')
                    
                    UNION ALL
                    
                    SELECT id FROM diklat_hlc 
                    WHERE nrp = '$nrp' 
                    AND status != 'Tolak' 
                    AND tanggal_mulai >= '$today'
                    
                    UNION ALL
                    
                    SELECT id FROM diklat_eksternal 
                    WHERE nrp = '$nrp' 
                    AND status != 'Tolak' 
                    AND tanggal_mulai >= '$today'
                ) as combined_jadwal"))->count();
            } catch (\Exception $e) {
                $countJadwal = 0;
            }

            // 3. INBOX / UNDANGAN (User)
            // Inbox adalah item yang membutuhkan aksi user (Belum disetujui/diterima)
            // PENTING: Sesuaikan status 'Menunggu Persetujuan' dengan status aktual di DB Anda.
            // Kadang statusnya 'Terjadwal', 'Pending', atau 'Diundang'.
            try {
                $statusInbox = ['Menunggu Persetujuan', 'Terjadwal', 'Pending', 'Diundang']; // Tambahkan status lain jika perlu
                
                $countInbox = DB::table(DB::raw("(
                    SELECT id FROM diklat_hlc 
                    WHERE nrp = '$nrp' 
                    AND status IN ('" . implode("','", $statusInbox) . "')
                    
                    UNION ALL
                    
                    SELECT id FROM diklat_eksternal 
                    WHERE nrp = '$nrp' 
                    AND status IN ('" . implode("','", $statusInbox) . "')
                ) as combined_inbox"))->count();
            } catch (\Exception $e) {
                $countInbox = 0;
            }
        }

        // 4. IMPOSTOR DATA
        $impersonatorName = null;
        if ($request->session()->has('impersonator_id')) {
            $impersonatorName = optional(User::where('nrp', $request->session()->get('impersonator_id'))->first())->name;
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'nrp' => $request->user()->nrp,
                    'employee_id' => $request->user()->employee_id,
                    'roles' => $request->user()->getRoleNames(),
                ] : null,
            ],
            'is_impersonating' => $request->session()->has('impersonator_id'),
            'impersonatorName' => $impersonatorName,
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'notifications' => [
                'jadwal_count' => $countJadwal,
                'persetujuan_count' => $countPersetujuan,
                'inbox_count' => $countInbox, // Key ini harus konsisten
            ],
        ];
    }
}