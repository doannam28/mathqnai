<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeysTag extends Model
{
    protected $table = 'keys_tag';

    protected $fillable = [
        'keys_id',
        'taxonomyitem_id',
        'number',
    ];

    public function taxonomyitem()
    {
        return $this->belongsTo(Taxonomyitem::class, 'taxonomyitem_id');
    }

    public function key()
    {
        return $this->belongsTo(Keys::class, 'keys_id');
    }
}
