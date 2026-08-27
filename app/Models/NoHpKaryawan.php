<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use \App\Traits\CustomSoftDelete;

class NoHpKaryawan extends Model
{
    use CustomSoftDelete;
    protected $table = 'no_hp_karyawan';
    protected $fillable = [
        'nama',
        'nomor_wa',
        'bagian',
        'nrp',
        'email',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];
    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Mutator untuk memastikan nomor WA selalu format 62
     */
    protected function nomorWa(): Attribute
    {
        return Attribute::make(
            set: function (string $value) {
                // Hapus karakter selain angka
                $nomor = preg_replace('/[^0-9]/', '', $value);

                // Ubah 08... atau +62... menjadi 628...
                if (str_starts_with($nomor, '0')) {
                    return '62' . substr($nomor, 1);
                } elseif (str_starts_with($nomor, '8')) {
                    return '62' . $nomor;
                }

                return $nomor;
            },
        );
    }
}