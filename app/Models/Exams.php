<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exams extends Model {
    protected $fillable = [];
    protected $table = 'exams';
    public function category()
    {
        return $this->belongsTo(Keys::class, 'key_id');
    }
    public function questions()
    {
        return $this->hasMany(KeysTag::class, 'question_id');
    }
}

