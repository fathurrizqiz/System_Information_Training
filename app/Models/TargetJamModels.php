<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetJamModels extends Model
{
    protected $table = 'target_jam_datamaster';
    protected $fillable = [
        'kategori', 
        'target_jam'
    ];
}
