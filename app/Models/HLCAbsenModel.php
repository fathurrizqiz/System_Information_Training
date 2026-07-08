<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HLCAbsenModel extends Model
{
    protected $table = 'diklat_hlc_kehadiran';

    protected $fillable = [
        'diklat_hlc_id',
        'tanggal',
        'status'
    ];

    /**
     * Relasi ke diklat HLC
     */
    public function diklat()
    {
        return $this->belongsTo(HLCManajement::class, 'diklat_hlc_id');
    }
}
