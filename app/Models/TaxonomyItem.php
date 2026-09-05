<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxonomyItem extends BaseModel
{
    use HasFactory;

    public function taxonomy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
