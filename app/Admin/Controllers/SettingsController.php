<?php

namespace App\Admin\Controllers;

use App\Models\Settings;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;

class SettingsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Settings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Settings());
        $grid->disableCreation();
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('site_title', __('Title website'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });
        return $grid;
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail()
    {
        $show = new Show(Settings::findOrFail(1));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('site_title', __('Title website'));
        $show->field('meta_description', __('Meta description'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Settings());
        $setting = Settings::first();
        $form->text('name','Tên website');
        $form->image('logo', __('Logo'));
        $form->image('favicon', __('Favicon'));
        $form->image('image_og', __('Ảnh show trên social'));
        $form->text('site_title', __('Tiêu đề website'));
        $form->text('meta_description', __('Meta description'));
        //$form->image('img_soicau', __('Img soi cầu'));

        //$form->tinyEditor('soicau1', __('Soi cầu 2'))->default($content->soicau1 ?? "");
        //$form->tinyEditor('text_run', __('Text chạy'))->default($content->text_run ?? "");

        return $form;
    }
}
