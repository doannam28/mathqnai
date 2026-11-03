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
use function Termwind\ValueObjects\pr;


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
            if($uri[1] == 'danh-muc-toan'){
                $vid = 1;
            }
            if($uri[1] == 'truong-lop-hoc'){
                $vid = 2;
            }
        }

        $grid = new Grid(new TaxonomyItem());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');

        //filter
        $grid->filter(function ($filter) use ($vid) {
            $filter->disableIdFilter();
            $filter->like('title', __('Name'));
            if($vid == 2) {
                $filter->equal('parent_id', __('Trường'))->select(
                    TaxonomyItem::where('taxonomy_id', 2)->where('parent_id', null)->pluck('name', 'id')
                );
            }
            $filter->equal('status', __('Trạng thái'))->select([1 => 'Kích hoạt', 0 => 'Không kích hoạt']);
        });

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('slug', __('Slug'));
        $grid->column('description', __('Mô tả'));
        $grid->column('order', __('Thứ tự'))->text();
        if(isset($uri[1]) && $uri[1] == 'truong-lop-hoc') {
            $grid->column('parent_id', __('Thuộc trường'))->display(function () {
                $cat = TaxonomyItem::where('id', $this->parent_id)->first();
                return isset($cat->name) ? $cat->name : '';
            });
        }
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
            if($uri[1] == 'danh-muc-toan'){
                $vid = 1;
            }
            if($uri[1] == 'truong-lop-hoc'){
                $vid = 2;
            }
        }

        $form = new Form(new TaxonomyItem());
        $form->text('name', __('Name'));
        $form->text('slug', __('Slug'));
        $form->textarea('description', __('Mô tả'));
        if(isset($uri[1]) && $uri[1] == 'truong-lop-hoc') {
            $cats = Taxonomyitem::where('taxonomy_id', $vid)->where('parent_id',null)->orderBy('order', 'ASC')->get();
            $listdatas = array();
            foreach ($cats as $k => $v) {
                $tmp['id'] = $v['id'];
                $tmp['name'] = $v['name'];
                $tmp['parent'] = $v['parent_id'];
                $listdatas[$k] = $tmp;
            }
            $tree = array();
            $listdatas1 = $listdatas;
            $listree = Utility::listtree($listdatas1, 0, $tree);
            $options = [];
            foreach ($listree as $k => $row) {
                $options[$row['id']] = $row['name'];
            }
            $form->select('parent_id', __('Chọn trường'))
                ->options($options);
        }
        $form->number('order', __('Thứ tự'));
        $form->switch('status', __('Status'))->default(1);
        //$form->switch('menu', __('Menu'))->default(0);
        $form->hidden('taxonomy_id')->value($vid);

        $form->saving(function (\Encore\Admin\Form $form) {
            $request = Request::all();
            if (!isset($request["_edit_inline"]) && !empty($form->name)) {
                if (empty(trim(strip_tags($form->slug)))) {
                    $form->slug = Utility::slug(trim(strip_tags($form->name)), "taxonomyitem", $form->model()->id);
                } else {
                    $count = TaxonomyItem::where('slug', $form->slug)->where('id', '<>', $form->model()->id)->count();
                    if ($count) {
                        $form->slug = Utility::slug(trim(strip_tags($form->name)), "taxonomyitem");
                    }
                }
            }
        });
        return $form;
    }
}
