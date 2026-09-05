<?php

namespace App\Services;

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
use App\Models\User;
use App\Models\WaTemplate;
use Illuminate\Support\Collection;

class TrashService
{
    /**
     * Konfigurasi Model Terpusat (Registry Pattern)
     * Menambahkan model baru di masa depan cukup dengan menambahkan entri di array ini.
     */
    protected array $models = [
        'diklatkaryawan' => [
            'class' => DiklatKaryawan::class,
            'label' => 'Diklat Karyawan',
            'name_column' => 'nama_diklat',
            'detail_column' => 'nrp',
            'admin_only' => false,
            'user_filtered' => true,
        ],
        'diklateksternal' => [
            'class' => DiklatEksternal::class,
            'label' => 'Diklat Eksternal',
            'name_column' => ['nama_diklat', 'nama_program'],
            'detail_column' => 'nrp',
            'admin_only' => false,
            'user_filtered' => true,
        ],
        'diklathlc' => [
            'class' => HLCManajement::class,
            'label' => 'Diklat HLC',
            'name_column' => 'nama_diklat',
            'detail_column' => 'nrp',
            'admin_only' => false,
            'user_filtered' => true,
        ],
        'library' => [
            'class' => MateriModel::class,
            'label' => 'Materi Library',
            'name_column' => 'title',
            'detail_column' => null,
            'admin_only' => false,
            'user_filtered' => false,
        ],
        'diklatinternal' => [
            'class' => DetailInternal::class,
            'label' => 'Diklat Internal',
            'name_column' => 'nama_diklat',
            'detail_column' => null,
            'admin_only' => true,
            'user_filtered' => false,
        ],
        'karyawans' => [
            'class' => Karyawans::class,
            'label' => 'Data Karyawan',
            'name_column' => 'nama_karyawan',
            'detail_column' => 'nrp',
            'admin_only' => true,
            'user_filtered' => false,
        ],
        'noHP' => [
            'class' => NoHpKaryawan::class,
            'label' => 'No HP',
            'name_column' => 'nama',
            'detail_column' => 'no_hp',
            'admin_only' => true,
            'user_filtered' => false,
        ],
        'programInternal' => [
            'class' => PendidikanFormalModels::class,
            'label' => 'Program Internal',
            'name_column' => 'nama_program',
            'detail_column' => null,
            'admin_only' => true,
            'user_filtered' => false,
        ],
        'programEksternal' => [
            'class' => ProgramEksternal::class,
            'label' => 'Program Eksternal',
            'name_column' => 'nama_program',
            'detail_column' => null,
            'admin_only' => true,
            'user_filtered' => false,
        ],
        'programHLC' => [
            'class' => ProgramHlc::class,
            'label' => 'Program HLC',
            'name_column' => 'nama_program',
            'detail_column' => null,
            'admin_only' => true,
            'user_filtered' => false,
        ],
        'waTemplate' => [
            'class' => WaTemplate::class,
            'label' => 'WA Template',
            'name_column' => 'nama_template',
            'detail_column' => null,
            'admin_only' => true,
            'user_filtered' => false,
        ],
    ];

    /**
     * Mengambil seluruh data terhapus sesuai role user
     */
    public function getAllTrash(?User $user = null): Collection
    {
        $trash = collect();
        $isAdmin = $user ? $user->hasAnyRole(['admin_diklat', 'super-admin']) : true;
        $nrp = $user?->nrp;

        foreach ($this->models as $type => $config) {
            // Jika data khusus admin dan user bukan admin, lewati
            if ($config['admin_only'] && !$isAdmin) {
                continue;
            }

            /** @var \Illuminate\Database\Eloquent\Model $modelClass */
            $modelClass = $config['class'];
            $query = $modelClass::isDeleted();

            // Jika user biasa dan data perlu difilter berdasarkan NRP pemilik
            if (!$isAdmin && !empty($config['user_filtered'])) {
                if (!empty($nrp)) {
                    $query->where('nrp', $nrp);
                } else {
                    continue;
                }
            }

            $items = $query->get()->map(function ($item) use ($type, $config) {
                // Resolusi nama data
                $nama = '-';
                if (is_array($config['name_column'])) {
                    foreach ($config['name_column'] as $col) {
                        if (!empty($item->{$col})) {
                            $nama = $item->{$col};
                            break;
                        }
                    }
                } else {
                    $nama = $item->{$config['name_column']} ?? '-';
                }

                // Resolusi detail data (NRP / No HP / lainnya)
                $detail = null;
                if (!empty($config['detail_column']) && !empty($item->{$config['detail_column']})) {
                    $val = $item->{$config['detail_column']};
                    $detail = $config['detail_column'] === 'nrp' ? "NRP: {$val}" : $val;
                }

                return [
                    'id' => $item->id,
                    'nama_data' => $nama,
                    'type' => $type,
                    'type_label' => $config['label'],
                    'detail' => $detail,
                    'deleted_by' => $item->deleted_by,
                    'deleted_at' => $item->deleted_at,
                ];
            });

            $trash = $trash->concat($items);
        }

        return $trash->sortByDesc('deleted_at')->values();
    }

    /**
     * Dapatkan nama class Model berdasarkan string type
     */
    public function getModelClass(string $type): ?string
    {
        return $this->models[$type]['class'] ?? null;
    }

    /**
     * Restore item dari trash
     */
    public function restore(string $type, int $id): bool
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return false;
        }

        $item = $modelClass::where('id', $id)->where('is_deleted', true)->first();
        if (!$item) {
            return false;
        }

        $item->restoreFromTrash();
        return true;
    }

    /**
     * Hapus permanen item
     */
    public function forceDelete(string $type, int $id): bool
    {
        $modelClass = $this->getModelClass($type);
        if (!$modelClass) {
            return false;
        }

        $item = $modelClass::find($id);
        if (!$item) {
            return false;
        }

        $item->delete();
        return true;
    }
}

