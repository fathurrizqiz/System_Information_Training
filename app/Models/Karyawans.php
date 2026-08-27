<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDelete;

class Karyawans extends Model
{
    use CustomSoftDelete;
    public $timestamps = false;
    protected $table = 'karyawans';
    protected $fillable = [
        'nama_karyawan',
        'user_id',
        'tmt',
        'nrp',
        'bagian',
        'unit_kerja',
        'posisi_jabatan',
        'klinis_non_klinis',
        'jenis_kelamin',
        'tanggal_mulai_akumulasi_promosi',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];

        protected $casts = [
        'tmt' => 'date',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];
    
    // MasterDataModels.php

    public function diklatInternalUtama()
    {
        return $this->hasMany(PeriodeBagianDetailInternal::class, 'nrp', 'nrp');
    }
    public function diklat()
    {
        return $this->hasMany(DiklatKaryawan::class, 'karyawan_id', 'id', );
    }

    public function diklatByNrp()
    {
        return $this->hasMany(DiklatKaryawan::class, 'nrp', 'nrp');
    }
    public function diklatHlc()
    {
        return $this->hasMany(HLCManajement::class, 'nrp', 'nrp');
    }
    public function diklatEksternal()
    {
        return $this->hasMany(DiklatEksternal::class, 'nrp', 'nrp');
    }

}
