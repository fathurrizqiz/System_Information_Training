<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class SuperAdminController extends Controller
{
    public function home()
    {
        return Inertia::render('SuperAdmin/Home', [
            'message' => 'Selamat datang di dashboard Super Admin!',
        ]);
    }
    public function index()
    {
        $user =  User::with('roles')->get();
        $all = Role::all();
        return Inertia::render('SuperAdmin/Management', [
            // Ambil semua role dari database Spatie
            'allRoles' => $all,
            'users' => $user,
        ]);
    }

    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => $request->name]);
        return back()->with('success', 'Role baru berhasil dibuat!');
    }

    // SuperAdminController.php
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'sometimes|array',
        ]);

        $roles = $request->input('roles', []);

        $user->syncRoles($roles);

        return back()->with('success', 'Role berhasil diperbarui!');
    }
}
