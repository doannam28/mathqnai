<?php

namespace App\Admin\Controllers;

use App\Helpers\Utility;
use App\Models\Category;
use App\Models\Question;
use App\Models\TaxonomyItem;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Question';
    protected $ajax = true;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        \Admin::css('/assets/css/admin-question.css');
        $grid = new Grid(new Question());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        //filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('level', __('Level'))->select([0 => 'Dễ', 1 => 'Trung bình', 2 => 'Khó']);
            $filter->equal('cat_id', __('Danh mục toán'))->select( \App\Models\Taxonomyitem::where('taxonomy_id', 1)
                ->pluck('name', 'id'));
        });

        $grid->column('id', __('Id'));
        $grid->column('title', __('Tiêu đề'));
        $grid->column('cat_id', __('Danh mục toán'))->display(function(){
            $cat = TaxonomyItem::where('id',$this->cat_id)->first();
            return isset($cat->name)?$cat->name:'';
        });
        $grid->column('level', __('Level'))->display(function(){
            if($this->level == 1) return "Trung Bình";
            if($this->level == 2) return "Khó";
            return "Dễ";
        });
        $grid->column('content', __('Câu hỏi'))
            ->display(function ($content) {
                return '
            <div class="question-content">
                '.$content.'
            </div>
        ';
            });
        $grid->column('result', __('Câu trả lời'));
        $grid->column('order', __('Order'))->editable()->sortable();
        $grid->column('status', __('Trạng thái'))->switch();


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
        return redirect("/admin/questions/$id/edit");
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        $form = new Form(new Question());
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
        $form->text('title', __('Tiêu đề'));
        $mang = ["0"=>"Dễ","1"=>"Trung bình","2"=>"Khó"];
        $form->select('level', 'Level')
            ->options($mang)
            ->required();
        $form->select('cat_id', 'Danh mục toán')
            ->options(
                \App\Models\Taxonomyitem::where('taxonomy_id', 1)
                    ->pluck('name', 'id')
            )
            ->required();
        $form->tinyEditor('content', __('Câu hỏi'));
        $form->text('result', __('Câu trả lời'))->required();
        $form->number('order', __('Vị trí'))->default(0);
        $form->switch('status', __('Trạng thái'))->default(1);
        $form->saving(function (\Encore\Admin\Form $form) {
            $request = Request::all();
            if(!isset($request["_edit_inline"]) && !empty($form->title)) {
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
