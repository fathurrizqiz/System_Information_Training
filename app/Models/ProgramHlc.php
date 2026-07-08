<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramHlc extends Model
{
    protected $table = 'program_diklat_hlc';
    protected $fillable = [
        'nama_program',
        'tahun'
    ];

    public function hlc()
    {
        return $this->hasMany(HLCManajement::class, 'program_id');
    }

}
