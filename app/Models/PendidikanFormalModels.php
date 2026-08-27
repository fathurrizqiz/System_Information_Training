<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDelete;

class PendidikanFormalModels extends Model
{
    use CustomSoftDelete;
    protected $table = 'program_internal';
    protected $fillable = [
        'nama_program',
        'kategori',
        'tahun',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];

    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(DetailInternal::class, 'program_id');
    }
   
}
