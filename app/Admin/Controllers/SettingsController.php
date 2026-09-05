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
        $grid->column('email', __('Email'));
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
        $show->field('phone', __('Hotline'));
        $show->field('phone_display', __('Hiển thị hotline'));
        $show->field('phone2', __('Hotline2'));
        $show->field('phone2_display', __('Hiển thị hotline2'));
        $show->field('address', __('Địa chỉ'));
        $show->field('facebook', __('Facebook'));
        $show->field('youtube', __('Link youtube'));
        $show->field('tiktok', __('Link tiktok'));
        $show->field('email_receive', __('Email nhận liên hệ'));
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
        $content = isset($setting->content) ? json_decode($setting->content): '';
        $form->text('name','Tên website');
        $form->text('address', __('Địa chỉ'));
        $form->text('phone', __('Hotline'));
        $form->text('zalo', __('Zalo'))->default($content->zalo ?? "");
        $form->text('telegram', __('Telegram'));
        $form->text('email', __('Email'));
        $form->text('email_receive', __('Email nhận liên hệ'));
        $form->image('logo', __('Logo'));
        $form->image('logo_footer', __('Logo footer'));
        $form->image('favicon', __('Favicon'));
        $form->image('image_og', __('Ảnh show trên social'));
        $form->image('img_soicau', __('Ảnh giới thiệu'));
        $form->tinyEditor('soicau', __('Giới thiệu'))->default($content->soicau ?? "");
        $form->text('title1', __('Tiêu đề sản phẩm dịch vụ 1'))->default($content->title1 ?? "");
        $form->tinyEditor('text1', __('Sản phẩm dịch vụ 1'));
        $form->text('title2', __('Tiêu đề sản phẩm dịch vụ 2'))->default($content->title2 ?? "");
        $form->tinyEditor('text2', __('Sản phẩm dịch vụ 2'));
        $form->text('title3', __('Tiêu đề sản phẩm dịch vụ 3'))->default($content->title3 ?? "");
        $form->tinyEditor('texttag', __('Sản phẩm dịch vụ 3'));
        $form->image('img_why', __('Ảnh tại sao chọn chúng tôi'));
        $form->tinyEditor('copyright', __('Tại sao chọn chúng tôi'));
        $form->tinyEditor('textfooter', __('Text footer'));

        $form->text('site_title', __('Tiêu đề website'));
        $form->text('meta_description', __('Meta description'));
        $form->hidden('content', __('Content'));
        //$form->image('img_soicau', __('Img soi cầu'));

        //$form->tinyEditor('soicau1', __('Soi cầu 2'))->default($content->soicau1 ?? "");
        //$form->tinyEditor('text_run', __('Text chạy'))->default($content->text_run ?? "");
        $form->submitted(function (Form $form) {
            $content = [];
            $content['soicau'] = Request::input('soicau');
            $content['title1'] = Request::input('title1');
            $content['title2'] = Request::input('title2');
            $content['title3'] = Request::input('title3');
            $content['zalo'] = Request::input('zalo');
            //$content['soicau1'] = Request::input('soicau1');
            //$content['text_run'] = Request::input('text_run');
            $form->content = json_encode($content);
            $form->ignore('soicau');
            $form->ignore('title1');
            $form->ignore('title2');
            $form->ignore('title3');
            $form->ignore('zalo');
            //$form->ignore('soicau1');
            //$form->ignore('text_run');
        });
        return $form;
    }
}
