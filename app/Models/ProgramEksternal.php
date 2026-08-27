<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDelete;

class ProgramEksternal extends Model
{
    use CustomSoftDelete;
    protected $table = 'program_diklat_eksternal';
    protected $fillable = [
        'nama_diklat',
        'tahun',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];
    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function eksternal()
    {
        return $this->hasMany(DiklatEksternal::class, 'program_id');
    }
}
