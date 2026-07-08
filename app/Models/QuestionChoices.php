<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionChoices extends Model
{
    protected $table = 'question_choices';

    protected $fillable = [
        'question_id',
        'text',
        'is_correct'
    ];

    public function question()
    {
        return $this->belongsTo(QuestionTestDetailInternal::class, 'question_id');
    }
}
