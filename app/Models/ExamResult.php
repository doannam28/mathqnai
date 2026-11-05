<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $table = 'exam_results';

    protected $fillable = [
        'attempt_id',
        'exam_id',
        'question_id',
        'user_id',
        'answer',
        'correct_answer',
        'is_correct',
    ];
}
