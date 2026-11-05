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
        \Admin::css('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined');
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
        $isMathCategory = $vid == 1;

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
        if ($isMathCategory) {
            $grid->column('icon_name', __('Icon'))->display(function ($iconName) {
                if (empty($iconName)) {
                    return '';
                }

                $color = $this->color ?: '#2563eb';
                return '<span class="material-symbols-outlined" style="font-size: 24px; line-height: 1; color: ' . e($color) . ';">' . e($iconName) . '</span>';
            });
            $grid->column('color', __('Color'))->display(function ($color) {
                $color = $color ?: '#2563eb';
                return '<span style="display:inline-flex;align-items:center;gap:8px;"><span style="width:18px;height:18px;border-radius:999px;background:' . e($color) . ';display:inline-block;border:1px solid rgba(0,0,0,.08);"></span><span>' . e($color) . '</span></span>';
            });
        }
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
        $show->field('icon_name', __('Icon name'));
        $show->field('color', __('Color'));
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
        \Admin::css('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined');
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
        $isMathCategory = $vid == 1;

        $form = new Form(new TaxonomyItem());
        $form->text('name', __('Name'));
        if ($isMathCategory) {
            $iconOptions = [
                'calculate' => 'Đại số',
                'functions' => 'Hàm số',
                'percent' => 'Phần trăm',
                'straighten' => 'Hình học',
                'square_foot' => 'Diện tích',
                'rule' => 'Thước đo',
                'menu_book' => 'Sách',
                'school' => 'Học tập',
                'science' => 'Khoa học',
                'psychology' => 'Tư duy',
                'dataset' => 'Thống kê',
                'grid_3x3' => 'Bảng lưới',
                'timeline' => 'Dãy số',
                'leaderboard' => 'Bảng xếp hạng',
                'format_list_numbered' => 'Danh sách',
                'question_mark' => 'Câu hỏi',
                'lightbulb' => 'Ý tưởng',
                'bookmark' => 'Đánh dấu',
                'check_circle' => 'Đúng',
                'star' => 'Nổi bật',
            ];

            $form->select('icon_name', __('Icon name'))
                ->options($iconOptions)
                ->default('calculate')
                ->help('Chọn icon Google Material Symbols cho dạng toán.');
            $form->text('color', __('Color'))
                ->attribute('type', 'color')
                ->default('#2563eb')
                ->help('Chọn mã màu hiển thị cho dạng toán.');
            $form->html('<div class="alert alert-info mt-2 mb-3" style="display:flex;align-items:center;gap:10px;"><span id="icon_name_preview" class="material-symbols-outlined" style="font-size: 28px; line-height: 1; color:#2563eb;">calculate</span><span>Icon và màu sẽ hiển thị như trên</span><span id="icon_color_preview" style="width:18px;height:18px;border-radius:999px;background:#2563eb;border:1px solid rgba(0,0,0,.08);display:inline-block;"></span></div>');
        }
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
        if ($isMathCategory) {
            \Admin::script(<<<'JS'
$(function () {
    var iconSelect = $('select[name="icon_name"]');
    var colorInput = $('input[name="color"]');
    var iconPreview = $('#icon_name_preview');
    var colorPreview = $('#icon_color_preview');

    function updateIconPreview() {
        if (!iconSelect.length || !iconPreview.length) return;
        var value = iconSelect.val() || 'calculate';
        iconPreview.text(value);
    }

    function updateColorPreview() {
        if (!colorInput.length || !colorPreview.length || !iconPreview.length) return;
        var value = colorInput.val() || '#2563eb';
        iconPreview.css('color', value);
        colorPreview.css('background-color', value);
    }

    iconSelect.on('change', updateIconPreview);
    colorInput.on('change input', updateColorPreview);
    updateIconPreview();
    updateColorPreview();
});
JS);
        }
        return $form;
    }
}
