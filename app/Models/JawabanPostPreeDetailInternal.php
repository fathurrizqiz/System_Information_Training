<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanPostPreeDetailInternal extends Model
{
    protected $table = 'jawaban_postes_preetest';
    protected $fillable = [
        'test_id',
        'nrp',
        'teks_choice',
        'is_correct'
    ];
}
