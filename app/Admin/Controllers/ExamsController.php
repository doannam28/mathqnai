<?php

namespace App\Admin\Controllers;

use App\Models\Exams;
use App\Models\Keys;
use App\Models\KeysTag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Request;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Storage;


class ExamsController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Từ khóa';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Exams());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('title', __('Tiêu đề'));
        $grid->column('keys_id', __('Thuộc mẫu đề'))->display(function () {
            $cat = Keys::where('id', $this->keys_id)->first();
            return isset($cat->title) ? $cat->title : '';
        });
        $grid->column('status', __('Status'))->switch();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Exams::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Tiêu đề'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        //$show->field('menu', __('Menu'))->using([0 => 'Inactive', 1 => 'Active']);
        //$show->field('taxonomy_id', __('Taxonomy id'));
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
        $cats = Keys::pluck('title', 'id');
        $form = new Form(new Exams());
        $form->text('title', __('Tiêu đề'));
        $form->select('keys_id', __('Chọn mẫu đề'))
            ->options($cats);
        $form->switch('status', __('Status'))->default(1);
        $form->saving(function (Form $form) {
            $key_tag = KeysTag::where('keys_id',$form->keys_id)->get();
            echo "<prev>";
            print_r($key_tag);
            echo "</prev>";
            die;
        });
        return $form;
    }
}
