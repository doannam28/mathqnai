<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keys extends Model {
    protected $fillable = [];
    protected $table = 'keys';

    public function keyTags()
    {
        return $this->hasMany(KeysTag::class, 'keys_id');
    }
}

