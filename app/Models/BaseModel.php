<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected function convertKey($inputs): array
    {
        $inputs = $inputs ?? [];
        $return = [];
        $i = 0;
        foreach ($inputs as $key => $input) {
            $return['old_'.$i] = $input;
            $i++;
        }
        return $return;
    }

}
