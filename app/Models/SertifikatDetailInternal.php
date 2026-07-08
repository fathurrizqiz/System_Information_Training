<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SertifikatDetailInternal extends Model
{
    protected $table = 'sertifikat_detail_diklat_internal';

    protected $fillable = [
        'detail_program_id',
        'materi'
    ];

    protected $casts = [
        'materi' => 'array',
    ];
}
