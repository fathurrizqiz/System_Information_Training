<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDelete;

class DiklatKaryawan extends Model
{
    use CustomSoftDelete;
    protected $table = 'diklat_karyawan';
    protected $fillable = [
        'nrp',
        'tanggal_mulai',
        'tanggal_selesai',
        'nama_diklat',
        'pengajar',
        'jam_diklat',
        'diklat',
        'file_path',
        'penyelenggara',
        'status',
        'alasan_penolakan',
        'evaluasimateri',
        'evaluasipengajar',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'deleted_at' => 'datetime',
    ];


    public function karyawan()
    {
        return $this->belongsTo(Karyawans::class, 'nrp', 'nrp');
    }

}
