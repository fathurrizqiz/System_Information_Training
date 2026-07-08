<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramEksternal extends Model
{
    protected $table = 'program_diklat_eksternal';
    protected $fillable = [
        'nama_diklat',
        'tahun'
    ];

    public function eksternal()
    {
        return $this->hasMany(DiklatEksternal::class, 'program_id');
    }
}
