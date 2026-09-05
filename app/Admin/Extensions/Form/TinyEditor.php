<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class TinyEditor extends Field
{
    public static $js = [
        '/packages/tinymce/tinymce.min.js'
    ];


    protected $view = 'admin.ckeditor';

    public function render(): Factory|string|View
{
    $version = filemtime(public_path('packages/setting.js'));

    $this->script = <<<EOT
$.getScript('/packages/setting.js?v={$version}', function() {
    if (typeof initEditor === 'function') {
        initEditor();
    }
});
EOT;

return parent::render();
}
}
