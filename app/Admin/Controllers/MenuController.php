<?php

namespace App\Admin\Controllers;

use App\Models\Menu;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;
use App\Helpers\Utility;


class MenuController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Menu';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Menu());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('link', __('Link'));
        $grid->column('order', __('Order'))->editable()->sortable();
        $grid->column('parent_id', __('Parent'))->display(function(){
           $cat = Menu::where('id',$this->parent_id)->first();
            return isset($cat->name)?$cat->name:'';
        });
        $grid->column('status', __('Status'))->switch();
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
        $show = new Show(Menu::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('link', __('Link'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
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
        $cats = Menu::orderBy('order','ASC')->get();
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
        $form = new Form(new Menu());
        $form->text('name', __('Name'));
        $form->text('link', __('Link'));
        $form->number('order', __('Order'));
        $form->switch('status', __('Status'))->default(1);
        //$form->switch('menu', __('Menu'))->default(0);
        $form->select('parent_id', __('Parent'))
            ->options($options);
        //$form->tinyEditor('content', __('Nôi dung'));
        return $form;
    }
}
