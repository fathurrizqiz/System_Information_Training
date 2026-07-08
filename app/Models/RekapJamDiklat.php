<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapJamDiklat extends Model
{
    protected $table = 'rekap_jam_diklat';
    protected $fillable = [
        'nrp',
        'bulan',
        'tahun',
        'total_jam'
    ];
}
