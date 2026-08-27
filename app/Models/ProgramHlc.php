<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDelete;

class ProgramHlc extends Model
{
    use CustomSoftDelete;
    protected $table = 'program_diklat_hlc';
    protected $fillable = [
        'nama_program',
        'tahun',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function hlc()
    {
        return $this->hasMany(HLCManajement::class, 'program_id');
    }

}
