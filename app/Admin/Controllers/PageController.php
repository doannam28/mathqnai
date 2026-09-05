<?php

namespace App\Admin\Controllers;

use App\Helpers\Utility;
use App\Models\Category;
use App\Models\Page;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PageController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Page';
    protected $ajax = true;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new Page());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        //filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('title', __('Tiêu đề'));
            $filter->equal('status', __('Trạng thái'))->select([1 => 'Kích hoạt', 0 => 'Không kích hoạt']);
        });

        $grid->column('id', __('Id'));
        $grid->column('title', __('Tiêu đề'));
        $grid->column('title_detail', __('Tiêu đề trang chi tiết'));
        $grid->column('slug', __('Link'));
        $grid->column('order', __('Order'))->editable()->sortable();
        $grid->column('status', __('Trạng thái'))->switch();
        $grid->column('menu', __('Menu'))->switch();
        $grid->column('created_at', __('Ngày tạo'))->display(function ($created_at) {
            return date('d/m/Y H:i', strtotime($created_at));
        });
        $grid->column('updated_at', __('Ngày cập nhật'))->display(function ($updated_at) {
            return date('d/m/Y H:i', strtotime($updated_at));
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     */
    protected function detail($id): \Illuminate\Foundation\Application|Redirector|RedirectResponse|Application
    {
        return redirect("/admin/pages/$id/edit");
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        $form = new Form(new Page());
        $form->setTitle($this->title);
        $form->tools(function ($tools) {
            $tools->disableView();
            $tools->disableDelete();
        });
        //hide footer
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        $form->text('title', __('Tiêu đề'))->required();
        $form->text('slug', __('Link'));
        //$form->text('name', __('Label'));
        $form->text('title_detail', __('Tiêu đề trang chi tiết'));
        $form->number('order', __('Vị trí'))->default(0);
        $form->image('image_og', __('Og image'))->rules('image|mimes:jpeg,png,jpg,gif,svg,webp');
        $form->textarea('meta_description', __('Meta description'));
        $form->tinyEditor('content', __('Nôi dung'));
        $form->switch('status', __('Trạng thái'))->default(1);
        $form->switch('menu', __('Menu'))->default(0);
        $form->saving(function (\Encore\Admin\Form $form) {
            $request = Request::all();
            if(!isset($request["_edit_inline"]) && !empty($form->title)) {
                if (empty(trim(strip_tags($form->slug)))) {
                    $form->slug = Utility::slug(trim(strip_tags($form->title)), "page", $form->model()->id);
                } else {
                    $count = Page::where('slug', $form->slug)->where('id', '<>', $form->model()->id)->count();
                    if ($count) {
                        $form->slug = Utility::slug(trim(strip_tags($form->title)), "page");
                    }
                }
                if(!empty($form->content)){
                    $content = $form->content;
                    $content = str_replace("../../../uploads","/uploads", $content);
                    $content = str_replace("../../uploads","/uploads", $content);
                    $form->content = str_replace("../uploads","/uploads", $content);
                }
            }
        });
        return $form;
    }
}
