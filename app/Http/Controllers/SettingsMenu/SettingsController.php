<?php

namespace App\Http\Controllers\SettingsMenu;

use App\Http\Controllers\Controller;
use App\Models\DetailInternal;
use App\Models\DiklatEksternal;
use App\Models\DiklatKaryawan;
use App\Models\HLCManajement;
use App\Models\Karyawans;
use App\Models\MateriModel;
use App\Models\NoHpKaryawan;
use App\Models\PendidikanFormalModels;
use App\Models\ProgramEksternal;
use App\Models\ProgramHlc;
use App\Models\WaTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Jadwal/MenuSettings/index');
    }
    public function trash()
    {
        $user = auth()->user();
        $nrp = $user->nrp; // langsung dari kolom users.nrp

        $isAdmin = $user->hasAnyRole(['admin_diklat', 'super-admin']);
        $isUser = $user->hasAnyRole(['karyawan']);

        $data = [
            'diklatkaryawan' => [],
            'diklateksternal' => [],
            'diklathlc' => [],
            'library' => [],
            'diklatinternal' => [],
            'karyawans' => [],
            'noHP' => [],
            'programInternal' => [],
            'programEksternal' => [],
            'programHLC' => [],
            'waTemplate' => []
        ];

        if ($isAdmin) {
            $data['diklateksternal'] = DiklatEksternal::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['diklathlc'] = HLCManajement::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['diklatinternal'] = DetailInternal::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['karyawans'] = Karyawans::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['noHP'] = NoHpKaryawan::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['programInternal'] = PendidikanFormalModels::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['programEksternal'] = ProgramEksternal::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['programHLC'] = ProgramHlc::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['waTemplate'] = WaTemplate::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['diklatkaryawan'] = DiklatKaryawan::Isdeleted()->orderBy('deleted_at', 'desc')->get();
            $data['library'] = MateriModel::Isdeleted()->orderBy('deleted_at', 'desc')->get();
        } elseif($isUser) {
            $data['library'] = MateriModel::Isdeleted()->orderBy('deleted_at', 'desc')->get();

            if (!empty($nrp)) {
                $data['diklatkaryawan'] = DiklatKaryawan::Isdeleted()->where('nrp', $nrp)->orderBy('deleted_at', 'desc')->get();
                $data['diklateksternal'] = DiklatEksternal::Isdeleted()->where('nrp', $nrp)->orderBy('deleted_at', 'desc')->get();
                $data['diklathlc'] = HLCManajement::Isdeleted()->where('nrp', $nrp)->orderBy('deleted_at', 'desc')->get();
            } else {
                \Log::warning('User ' . $user->id . ' tidak memiliki NRP. Data diklat tidak ditampilkan.');
            }
        }

        return Inertia::render('Jadwal/MenuSettings/trash', $data);
    }


    // Restore dari recycle bin
    public function restore(Request $request, string $type, int $id)
    {
        if (!$type) {
            return redirect()->back()->with('error', 'Tipe data tidak valid!');
        }

        $model = $this->getModelByType($type);

        if (!$model) {
            return redirect()->back()->with('error', "Model untuk tipe '{$type}' tidak ditemukan!");
        }

        // Cari data yang sedang di-soft delete
        $data = $model::where('id', $id)->where('is_deleted', true)->first();

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan di recycle bin!');
        }

        // Gunakan method custom dari trait
        $data->restoreFromTrash();

        return redirect()->back()->with('success', 'Data berhasil dipulihkan!');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        if (!$type) {
            return redirect()->back()->with('error', 'Tipe data tidak valid!');
        }

        $model = $this->getModelByType($type);

        if (!$model) {
            return redirect()->back()->with('error', "Model untuk tipe '{$type}' tidak ditemukan!");
        }

        // Cari data (boleh aktif atau terhapus, karena mau dihapus permanen)
        $data = $model::find($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!');
        }

        // Hapus permanen dari database
        $data->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus permanen!');
    }

    private function getModelByType(string $type): ?string
    {
        $map = [
            'diklatkaryawan' => DiklatKaryawan::class,
            'diklateksternal' => DiklatEksternal::class,
            'diklathlc' => HLCManajement::class,
            'library' => MateriModel::class,
            'diklatinternal' => DetailInternal::class,
            'karyawans' => Karyawans::class,
            'noHP' => NoHpKaryawan::class,
            'programInternal' => PendidikanFormalModels::class,
            'programEksternal' => ProgramEksternal::class,
            'programHLC' => ProgramHlc::class,
            'waTemplate' => WaTemplate::class,
        ];

        return $map[$type] ?? null;
    }
}
