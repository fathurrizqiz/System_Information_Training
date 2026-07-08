<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiDetailDiklat extends Model
{
    protected $table = 'presensi_detail_internal';
    protected $fillable = [
        'detail_program_id',
        'tanggal',
        'nama_karyawan',
        'nrp',
        'jam_diklat',
        'prescore',
        'postscore'
    ];
}
