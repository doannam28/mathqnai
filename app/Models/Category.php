<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $fillable = [];
    protected $table = 'categories';
    public function posts()
    {
        return $this->hasMany(Post::class, 'parent_id', 'id');
    }
}

