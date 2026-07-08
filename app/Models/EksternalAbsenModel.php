<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EksternalAbsenModel extends Model
{
    protected $table = 'diklat_eksternal_kehadiran';

    protected $fillable = [
        'diklat_eksternal_id',
        'tanggal',
        'status'
    ];

    /**
     * Relasi ke diklat eksternal
     */
    public function diklat()
    {
        return $this->belongsTo(DiklatEksternal::class, 'diklat_eksternal_id');
    }
}