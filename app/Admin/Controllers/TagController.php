<?php

namespace App\Admin\Controllers;

use App\Models\Tag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Storage;


class TagController extends BaseAdminController
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
        $grid = new Grid(new Tag());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('slug', __('Slug'));
        $grid->column('order', __('Order'))->editable()->sortable();
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
        $show = new Show(Tag::findOrFail($id));

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
        $cats = Tag::orderBy('order','ASC')->get();
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
        $form = new Form(new Tag());
        $form->text('name', __('Name'));
        $form->text('slug', __('Slug'));
        $form->number('order', __('Order'));
        $form->switch('status', __('Status'))->default(1);
        $form->image('image_og', __('Og image'))->rules('image|mimes:jpeg,png,jpg,gif,svg,webp');
        $form->textarea('meta_description', __('Meta description'));
        $form->saving(function (Form $form) {
            if(empty($form->slug)){
                $form->slug = Utility::slug($form->name, 'tag');
            }
        });
        return $form;
    }
}
