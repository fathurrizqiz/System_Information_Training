<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPromosi extends Model
{
    protected $table = 'riwayat_promosi';
    protected $fillable = [
        'nrp',
        'kategori',
        'target_jam',
        'jam_tercapai',
        'periode_mulai',
        'periode_selesai',
        'status',
        'tanggal_promosi',
    ];
}
