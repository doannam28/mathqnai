<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;
use Encore\Admin\Form\Field\ImageField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Images extends Field
{
    use ImageField;

    /**
     * {@inheritdoc}
     */
    protected $view = 'admin.images';
    public static $js = [
        '/packages/images.js'
    ];
    /**
     *  Validation rules.
     *
     * @var string
     */
    protected $rules = 'image';

    protected $images = [];

    public function render()
    {
        $this->name = $this->formatName($this->column);
        $this->addVariables([
            'images' => $this->form->model()?->images ?? [],
        ]);
        return parent::render();
    }

}
