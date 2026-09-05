<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Storage;


class CategoryController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Danh mục tin';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('slug', __('Slug'));
        $grid->column('order', __('Order'))->editable()->sortable();
        $grid->column('icon', __('Ảnh icon'))->display(function ($thumbnail) {
            if (!$thumbnail) return '';
            return "<img src='".Storage::disk('admin')->url($thumbnail)."' style='max-width: 100px; max-height: 100px;'>";
        });
       /* $grid->column('parent_id', __('Parent'))->display(function(){
           $cat = Category::where('id',$this->parent_id)->first();
            return isset($cat->name)?$cat->name:'';
        });*/
        $grid->column('status', __('Status'))->switch();
        $grid->column('menu', __('Show trang chủ'))->switch();
        //$grid->column('menu', __('Menu'))->switch();
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
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('slug', __('Slug'));
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
        $cats = Category::orderBy('order','ASC')->get();
        $listdatas = array();
        foreach($cats as $k => $v){
            $tmp['id'] = $v['id'];
            $tmp['name'] = $v['name'];
            $tmp['parent'] = $v['parent_id'];
            $listdatas[$k] = $tmp;
        }
        $tree =array();
        $listdatas1  = $listdatas;
        $listree = Utility::listtree($listdatas1,0,$tree);
        $options= [];
        foreach ($listree as $k=>$row) {
            $options[$row['id']] = $row['name'];
        }
        $form = new Form(new Category());
        $form->text('name', __('Name'));
        $form->text('slug', __('Slug'));
        $form->number('order', __('Order'));
        $form->switch('menu', __('Show trang chủ'))->default(1);
        $form->switch('status', __('Status'))->default(1);
        $form->image('icon', __('Hình ảnh icon'))->rules('image|mimes:jpeg,png,jpg,gif,svg,webp');
        $form->image('image_og', __('Og image'))->rules('image|mimes:jpeg,png,jpg,gif,svg,webp');
        $form->text('title_web', __('Title website'));
        $form->textarea('meta_description', __('Meta description'));
        //$form->switch('menu', __('Menu'))->default(0);
       /* $form->select('parent_id', __('Parent'))
            ->options($options);*/
        $form->tinyEditor('content', __('Nôi dung'));
        $form->saving(function (\Encore\Admin\Form $form) {
            $request = Request::all();
            if(!isset($request["_edit_inline"]) && !empty($form->name)) {
                if (empty(trim(strip_tags($form->slug)))) {
                    $form->slug = Utility::slug(trim(strip_tags($form->name)), "category", $form->model()->id);
                } else {
                    $count = Category::where('slug', $form->slug)->where('id', '<>', $form->model()->id)->count();
                    if ($count) {
                        $form->slug = Utility::slug(trim(strip_tags($form->name)), "category");
                    }
                }

            }
        });
        return $form;
    }
}
