<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostPreeDetailInternal extends Model
{
    protected $table = 'postest_preetest_detail_internal';
    protected $fillable = [
        'detail_program_id',
        'type'
    ];
    
    public function detailProgram()
    {
        return $this->belongsTo(DetailInternal::class, 'detail_program_id');
    }

    /**
     * Test ini punya banyak soal
     */
    public function questions()
    {
        return $this->hasMany(QuestionTestDetailInternal::class, 'test_id');
    }
}
