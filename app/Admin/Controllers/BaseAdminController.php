<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;

class BaseAdminController extends AdminController
{
    /**
     * @param $inputs
     * @return array
     */
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
