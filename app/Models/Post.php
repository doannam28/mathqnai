<?php

namespace App\Models;

class Post extends BaseModel
{
    const STATUS_ACTIVE = 1;

    protected $fillable = [
        'title',
        //'date_create',
        //'position',
        'content',
        'images',
        'meta',
        'slug',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }
}
