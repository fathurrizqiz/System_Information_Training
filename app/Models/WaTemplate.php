<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    protected $table = 'wa_templates';
    protected $fillable = [
        'nama_template',
        'slug',
        'pesan'
    ];
}
