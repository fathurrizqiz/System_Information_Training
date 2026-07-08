<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumentasiDetailInternal extends Model
{
    protected $table = 'dokumentasi_detail_internal';
    protected $fillable = [
        'detail_program_id',
        'file_path',
        'nama_file'
    ];

    public function detailProgram()
    {
        return $this->belongsTo(
            DetailInternal::class,
            'detail_id'
        );
    }
}
