<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    protected $table = 'exam_attempts';

    protected $fillable = [
        'exam_id',
        'user_id',
        'type',
        'taxonomy_item_id',
        'topic_title',
        'total_questions',
        'correct_count',
        'wrong_count',
        'score',
        'time_limit',
        'spent_seconds',
        'status',
        'started_at',
        'submitted_at',
        'question_ids',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];
}
