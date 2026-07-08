<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonateRequestModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter search dari URL/Request
        $search = $request->input('search');

        // Filter data user jika ada nilai search
        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('nrp', 'ILIKE', "%{$search}%");

            })
            ->get();
        return Inertia::render('SuperAdmin/UserManagement', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ]

        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nrp' => 'required|string|max:255|unique:users,nrp',
            'employee_id' => 'nullable|string|min:8',
            'password' => 'required|string|min:9|confirmed',
            'role' => 'required|string',
        ]);


        User::create([
            'name' => $request->name,
            'nrp' => $request->nrp,
            'employee_id' => $request->employee_id,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return back()->with('success', 'User baru berhasil dibuat!');
    }


    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // Set password sesuai NRP dan enkripsi menggunakan bcrypt
        $user->update([
            'password' => bcrypt($user->nrp)
        ]);

        return back()->with('success', 'Password berhasil di-reset menjadi default (NRP)!');
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User berhasil dihapus!');
    }

    // 1. Masuk sebagai User
    public function impersonate($id)
    {
        $admin = Auth::user();
        $userToImpersonate = User::findOrFail($id);

        // Hapus request lama yang masih pending untuk target yang sama
        ImpersonateRequestModel::where('target_nrp', $userToImpersonate->nrp)
            ->where('status', 'pending')
            ->delete();

        // Buat request baru
        $request = ImpersonateRequestModel::create([
            'admin_nrp' => $admin->nrp,
            'target_nrp' => $userToImpersonate->nrp,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(5),
        ]);

        return response()->json([
            'request_id' => $request->id,
            'expires_at' => $request->expires_at,
            'target_name' => $userToImpersonate->name,
        ]);
    }

    // function untuk cek status impersonate
    public function checkImpersonateStatus($requestId)
    {
        $impersonateRequest = ImpersonateRequestModel::findOrFail($requestId);

        // Auto-expire jika sudah lewat waktu
        if ($impersonateRequest->status === 'pending' && $impersonateRequest->isExpired()) {
            $impersonateRequest->update(['status' => 'expired']);
        }

        if ($impersonateRequest->status === 'approved') {
            // Lakukan login sekarang
            $userToImpersonate = User::where('nrp', $impersonateRequest->target_nrp)->firstOrFail();

            Session::put('impersonator_id', Auth::user()->nrp);
            Auth::guard('web')->login($userToImpersonate, false);
            Session::save();

            $impersonateRequest->delete();

            return response()->json(['status' => 'approved', 'redirect' => route('dashboard')]);
        }

        return response()->json(['status' => $impersonateRequest->status]);
    }

    // 2. Kembali ke Admin
    public function stopImpersonate()
    {
        $adminId = Session::get('impersonator_id');

        if (!$adminId) {
            return redirect('/');
        }

        // cari berdasarkan NRP atau ID, tergantung apa yang disimpan di session
        $admin = User::where('nrp', $adminId)
            ->orWhere('id', $adminId)
            ->firstOrFail();

        Session::forget('impersonator_id');
        Auth::guard('web')->login($admin, false);
        Session::save();

        // \Log::info('stopImpersonate - selesai', ['now_user' => Auth::id()]);

        return redirect('/super-admin/users')
            ->with('success', 'Kembali sebagai ' . $admin->name);
    }
}
