<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTestDetailInternal extends Model
{
    protected $table = 'question_test_detail_internal';
    protected $fillable = [
        'test_id',
        'pertanyaan',
        'tipe',
        'bobot'
    ];

    public function test()
    {
        return $this->belongsTo(PostPreeDetailInternal::class, 'test_id');
    }

    public function choices()
    {
        return $this->hasMany(QuestionChoices::class, 'question_id');
    }
}
