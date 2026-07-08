<?php

namespace App\Http\Controllers\Inbox;

use App\Http\Controllers\Controller;
use App\Models\DiklatEksternal;
use App\Models\HLCManajement;
use App\Models\ImpersonateRequestModel;
use App\Models\NoHpKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InboxController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $undangan = HLCManajement::where('nrp', $user->nrp)
            ->where('status', 'menunggu_persetujuan')
            ->whereDate('tanggal_selesai', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();
        $undanganexternal = DiklatEksternal::where('nrp', $user->nrp)
            ->where('status', 'menunggu_persetujuan')
            ->whereDate('tanggal_selesai', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get();
        $impersonateRequests = ImpersonateRequestModel::where('target_nrp', $user->nrp)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('admin')
            ->get();

        $belumTerdaftar = !NoHpKaryawan::where('nama', $user->name)->exists();

        if ($belumTerdaftar) {
            // Buat item tiruan yang formatnya mirip dengan item HLCManajement / Inbox Anda
            // Sesuaikan key-nya (seperti 'tipe', 'pesan', 'judul') dengan properti yang biasa dibaca di Vue/React front-end Anda
            $pesanPeringatan = (object) [
                'id' => 'warning-nohp', // ID unik buatan agar tidak bentrok
                'tipe' => 'system_warning',
                'judul' => 'Penting: Lengkapi Nomor WhatsApp',
                'pesan' => 'Anda belum mendaftarkan nomor HP/WhatsApp. Silakan isi terlebih dahulu untuk menerima notifikasi.',
                'url_action' => route('nohp.userrequest'), // Arahkan ke method userrequest() Anda
                'created_at' => now()->toISOString(),
            ];

            // Masukkan pesan peringatan ini ke urutan paling atas di dalam koleksi $undangan
            $undangan->prepend($pesanPeringatan);
        }
        return Inertia::render('Inbox/index', [
            'inboxItems' => $undangan,
            'inboxExternalItems' => $undanganexternal,
            'impersonateRequests' => $impersonateRequests,
        ]);
    }
    public function setujuRekomendasihlc($id)
    {
        $hlc = HLCManajement::findOrFail($id);

        // Ubah status menjadi pending (artinya masuk jadwal tapi belum dilaksanakan)
        $hlc->update(['status' => 'Setuju']);

        return redirect()->back()->with('success', 'Diklat berhasil ditambahkan ke jadwal Anda.');
    }
    public function tolakRekomendasihlc(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:255',
        ]);

        $hlc = HLCManajement::findOrFail($id);

        // Ubah status menjadi rejected dan simpan alasan
        $hlc->update([
            'status' => 'Tolak',
            'catatan_penolakan' => $request->alasan
        ]);

        return redirect()->back()->with('success', 'Undangan berhasil ditolak.');
    }
    public function setujuRekomendasieksternal($id)
    {
        $hlc = DiklatEksternal::findOrFail($id);

        // Ubah status menjadi pending (artinya masuk jadwal tapi belum dilaksanakan)
        $hlc->update(['status' => 'Setuju']);

        return redirect()->back()->with('success', 'Diklat berhasil ditambahkan ke jadwal Anda.');
    }
    public function tolakRekomendasieksternal(Request $request, $id)
    {
        // 1. Validasi input alasan agar tidak kosong
        $request->validate([
            'alasan' => 'required|string|max:255',
        ]);

        // 2. Cari data berdasarkan ID
        $eksternal = DiklatEksternal::findOrFail($id);

        // 3. Update status dan simpan catatan penolakan
        $eksternal->update([
            'status' => 'Tolak',
            'catatan_penolakan' => $request->alasan,
        ]);

        // 4. Redirect kembali dengan pesan sukses (Opsional: disesuaikan pesannya)
        return redirect()->back()->with('success', 'Undangan diklat eksternal berhasil ditolak.');
    }

    public function respondImpersonate(Request $request, $requestId)
    {
        $impersonateRequest = ImpersonateRequestModel::findOrFail($requestId);

        // Pastikan hanya target yang bisa respond
        if ($impersonateRequest->target_nrp !== Auth::user()->nrp) {
            abort(403);
        }

        // Cek expired
        if ($impersonateRequest->isExpired()) {
            $impersonateRequest->update(['status' => 'expired']);
            return back()->with('error', 'Request sudah kadaluarsa.');
        }

        $action = $request->input('action'); // 'approved' atau 'rejected'
        $impersonateRequest->update(['status' => $action]);

        return back()->with('success', $action === 'approved' ? 'Akses diberikan.' : 'Akses ditolak.');
    }

}
