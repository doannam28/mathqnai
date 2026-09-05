<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Taxonomy extends BaseModel
{
    use HasFactory;

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaxonomyItem::class);
    }
}
