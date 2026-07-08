<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePembahasanSertifikat extends Model
{
    protected $table='template_pembahasan_sertifikat';
    protected $fillable = [
        'periode_id',
        'materi'
    ];
    
}
