<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CustomSoftDelete;

class WaTemplate extends Model
{
    use CustomSoftDelete;
    protected $table = 'wa_templates';
    protected $fillable = [
        'nama_template',
        'slug',
        'pesan',
        'is_deleted',
        'deleted_at',
        'deleted_by'
    ];
    protected $casts = [
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];
}
