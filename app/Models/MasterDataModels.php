<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDataModels extends Model
{
    protected $table = 'master_data';
    protected $fillable = [
        'nama_karyawan',
        'tmt',
        'nrp',
        'bagian',
        'unit_kerja',
        'posisi_jabatan',
        'klinis_non_klinis',
        'jenis_kelamin',
    ];
    public $timestamps = false;
    protected $casts = [
        'tmt' => 'date',
        'klinis' => 'boolean',
    ];

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
    // MasterDataModels.php

    public function diklatInternalUtama()
    {
        return $this->hasMany(PeriodeBagianDetailInternal::class, 'nrp', 'nrp');
    }
}
