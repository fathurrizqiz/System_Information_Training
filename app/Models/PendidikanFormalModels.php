<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendidikanFormalModels extends Model
{
    protected $table = 'program_internal';
    protected $fillable = [
        'nama_program',
        'kategori',
        'tahun'
    ];

    public function details()
    {
        return $this->hasMany(DetailInternal::class, 'program_id');
    }
   
}
