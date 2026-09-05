<?php

namespace App\Admin\Controllers;

use App\Models\Taxonomy;
use App\Models\TaxonomyItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;
use App\Helpers\Utility;


class TaxonomyItemController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'TaxonomyItem';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $uri = explode('/',Request::path());
        $vid= 0;
        if(isset($uri[1])){
            if($uri[1] == 'locations'){
                $vid = intval(config('admin.category_location_id'));
            }else if($uri[1] == 'way-tours'){
                $vid = intval(config('admin.category_way_tour'));
            }
        }
        $grid = new Grid(new TaxonomyItem());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('slug', __('Slug'));
        $grid->column('order', __('Order'))->text();
        $grid->column('parent_id', __('Parent'))->display(function(){
           $cat = TaxonomyItem::where('id',$this->parent_id)->first();
            return isset($cat->name)?$cat->name:'';
        });
        $grid->column('status', __('Status'))->switch();
        //$grid->column('menu', __('Menu'))->switch();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->model()->where('taxonomy_id', $vid);

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
        $show = new Show(TaxonomyItem::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('slug', __('Slug'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('menu', __('Menu'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('taxonomy_id', __('Taxonomy id'));
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
        $uri = explode('/',Request::path());
        $vid= 0;
        if(isset($uri[1])){
            if($uri[1] == 'locations'){
                $vid = intval(config('admin.category_location_id'));
            }else if($uri[1] == 'way-tours'){
                $vid = intval(config('admin.category_way_tour'));
            }
        }
        $cats = Taxonomyitem::where('taxonomy_id',$vid)->orderBy('order','ASC')->get();
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
        $form = new Form(new TaxonomyItem());
        $form->text('name', __('Name'));
        $form->text('slug', __('Slug'));
        $form->number('order', __('Order'));
        $form->switch('status', __('Status'))->default(1);
        //$form->switch('menu', __('Menu'))->default(0);
        $form->hidden('taxonomy_id')->value($vid);
        $form->select('parent_id', __('Parent'))
            ->options($options);
        //$form->tinyEditor('content', __('Nôi dung'));
        $form->saving(function (Form $form) {
            if(empty($form->slug)){
                $form->slug = Utility::slug($form->name, 'taxonomyitem');
            }
        });
        return $form;
    }
}
