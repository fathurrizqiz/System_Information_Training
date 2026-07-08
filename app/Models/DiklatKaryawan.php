<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiklatKaryawan extends Model
{
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
        'evaluasipengajar'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];


    public function karyawan()
    {
        return $this->belongsTo(Karyawans::class, 'nrp', 'nrp');
    }

}
