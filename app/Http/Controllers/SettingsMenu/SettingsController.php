<?php

namespace App\Http\Controllers\SettingsMenu;

use App\Http\Controllers\Controller;
use App\Services\TrashService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    protected TrashService $trashService;

    public function __construct(TrashService $trashService)
    {
        $this->trashService = $trashService;
    }

    public function index()
    {
        return Inertia::render('Jadwal/MenuSettings/index');
    }

    public function trash()
    {
        return Inertia::render('Jadwal/MenuSettings/trash', [
            'trash' => $this->trashService->getAllTrash(auth()->user()),
        ]);
    }

    public function restore(Request $request, string $type, int $id)
    {
        if (!$type) {
            return redirect()->back()->with('error', 'Tipe data tidak valid!');
        }

        $success = $this->trashService->restore($type, $id);

        if (!$success) {
            return redirect()->back()->with('error', 'Data tidak ditemukan di recycle bin!');
        }

        return redirect()->back()->with('success', 'Data berhasil dipulihkan!');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        if (!$type) {
            return redirect()->back()->with('error', 'Tipe data tidak valid!');
        }

        $success = $this->trashService->forceDelete($type, $id);

        if (!$success) {
            return redirect()->back()->with('error', 'Data tidak ditemukan atau tipe tidak valid!');
        }

        return redirect()->back()->with('success', 'Data berhasil dihapus permanen!');
    }
}
