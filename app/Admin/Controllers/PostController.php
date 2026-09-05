<?php

namespace App\Admin\Controllers;

use App\Helpers\Utility;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Post';
    protected $ajax = true;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new Post());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        //filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('title', __('Tiêu đề'));
            $filter->equal('parent_id', __('Danh mục'))->select(
                Category::pluck('name', 'id')
            );
            $filter->equal('status', __('Trạng thái'))->select([1 => 'Kích hoạt', 0 => 'Không kích hoạt']);
        });

        $grid->column('id', __('Id'));
        $grid->column('title', __('Tiêu đề'));
        $grid->column('slug', __('Link'));
        /*$grid->column('thumbnail', __('Og image'))->display(function ($thumbnail) {
            if (!$thumbnail) return '';
            return "<img src='".Storage::disk('admin')->url($thumbnail)."' style='max-width: 100px; max-height: 100px;'>";
        });*/
        $grid->column('parent_id', __('Danh mục'))->display(function(){
            $cat = Category::where('id',$this->parent_id)->first();
            return isset($cat->name)?$cat->name:'';
        });
        $grid->column('status', __('Trạng thái'))->switch();
        $grid->column('hot', __('Tin hot'))->switch();
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
        return redirect("/admin/posts/$id/edit");
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {


        $form = new Form(new Post());
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
        $form->text('slug', __('Slug'));
        //$form->date('date_create', __('Chọn ngày đăng'));
        $form->number('position', __('Vị trí'))->default(0);
       /* $form->checkbox('categories', __('Danh mục'))->options(function (){
            return \App\Models\Category::all()->pluck('name', 'id');
        });*/
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
        $form->select('parent_id', __('Danh mục'))
            ->options($options);
        $form->image('thumbnail', __('Og image'))->rules('image|mimes:jpeg,png,jpg,gif,svg')
            //->help('<b style="color:red">(Nên upload ảnh có độ phân giải 1440x960)</b>')
            ->name(function ($file) {
                return \App\Files\Storage::getFileName($file);
            });
        $form->multipleSelect('tags', 'Từ khóa')
            ->options(Tag::pluck('name', 'id')->toArray());
        $form->tinyEditor('content', __('Nôi dung'));
        // Multiple Select tags

        $form->text('title_web', __('Title website'));
        $form->textarea('meta', __('Meta description'));
        $form->switch('status', __('Trạng thái'))->default(1);
        $form->switch('hot', __('Tin hot'))->default(10);
        $form->saving(function (\Encore\Admin\Form $form) {
            $request = Request::all();
            if(!isset($request["_edit_inline"]) && !empty($form->title)) {
                if (empty(trim(strip_tags($form->slug)))) {
                    $form->slug = Utility::slug(trim(strip_tags($form->title)), "post", $form->model()->id);
                } else {
                    $count = Post::where('slug', $form->slug)->where('id', '<>', $form->model()->id)->count();
                    if ($count) {
                        $form->slug = Utility::slug(trim(strip_tags($form->title)), "post");
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
