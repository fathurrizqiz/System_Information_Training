<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswerPostPreeDetail extends Model
{
    protected $table = 'user_answers_post_pree_detail';

    protected $fillable = [
        'question_id',
        'choice_id',
        'nrp',
        'essay_answer',
        'is_correct'
    ];

    public function question()
    {
        return $this->belongsTo(QuestionTestDetailInternal::class, 'question_id');
    }

    public function choice()
    {
        return $this->belongsTo(QuestionChoices::class, 'choice_id');
    }
}
