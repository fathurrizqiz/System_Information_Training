<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertanyaanPostPree extends Model
{
    protected $table = 'question_test_detail_internal';
    protected $fillable = [
        'test_id',
        'pertanyaan',
        'tipe'
    ];
}
