<?php

namespace App\Admin\Controllers;

use App\Helpers\Utility;
use App\Models\Category;
use App\Models\Keys;
use App\Models\Tag;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KeysController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Keys';
    protected $ajax = true;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {


        $grid = new Grid(new Keys());
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
        $grid->column('number', __('Tổng số câu'));

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
        return redirect("/admin/keys/$id/edit");
    }

/**
 * Make a form builder.
 *
 * @return Form
 */
    protected function form()
    {
        $form = new Form(new Keys());

        $form->setTitle($this->title);

        $form->tools(function ($tools) {
            $tools->disableView();
            $tools->disableDelete();
        });

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        $form->text('title', __('Tiêu đề'))->required();
        $form->text('number', __('Tổng số câu'))
            ->default(0)
            ->readonly()->attribute('style', 'width: 60px; text-align: center;');

        $form->switch('status', __('Trạng thái'))->default(1);


        // Cấu hình số câu theo từng danh mục
        $form->hasMany('keyTags', 'Cấu hình câu hỏi', function (Form\NestedForm $form) {

            $form->select('taxonomyitem_id', 'Danh mục toán')
                ->options(
                    \App\Models\Taxonomyitem::where('taxonomy_id', 1)
                        ->pluck('name', 'id')
                )
                ->required();

            $form->number('number', 'Số câu')
                ->default(1)
                ->min(1)
                ->required();
        });


        // Javascript
        // Javascript
        $form->html(<<<'HTML'
<script>
(function ($) {

    // =====================================================
    // Lấy tất cả taxonomyitem đã được chọn
    // =====================================================
    function getSelectedTaxonomyIds() {

        var selected = [];

        $("select[name*=\"[taxonomyitem_id]\"]").each(function () {

            var value = $(this).val();

            if (value) {
                selected.push(String(value));
            }

        });

        return selected;
    }


    // =====================================================
    // Disable các danh mục đã chọn
    // =====================================================
    function updateTaxonomyOptions() {

        var selected = getSelectedTaxonomyIds();

        $("select[name*=\"[taxonomyitem_id]\"]").each(function () {

            var currentSelect = $(this);
            var currentValue = currentSelect.val();

            currentSelect.find("option").each(function () {

                var option = $(this);
                var value = option.val();

                if (
                    value &&
                    String(value) !== String(currentValue)
                ) {

                    if (selected.indexOf(String(value)) !== -1) {
                        option.prop("disabled", true);
                    } else {
                        option.prop("disabled", false);
                    }

                } else {

                    option.prop("disabled", false);

                }

            });

            currentSelect.trigger("change.select2");

        });
    }


    // =====================================================
    // Tính tổng số câu
    // =====================================================
    function updateTotalQuestions() {

        var total = 0;

        $('input[name^="keyTags"][name$="[number]"]').each(function () {

            var value = parseInt($(this).val()) || 0;

            total += value;

        });

        // Field number của bảng keys
        $('input[name="number"]').val(total);
    }


    // =====================================================
    // Khi thay đổi danh mục
    // =====================================================
    $(document).on(
        "change",
        "select[name*=\"[taxonomyitem_id]\"]",
        function () {

            updateTaxonomyOptions();

        }
    );


    // =====================================================
    // Khi thay đổi Số câu
    // =====================================================
    $(document).on(
        "input change",
        'input[name^="keyTags"][name$="[number]"]',
        function () {

            updateTotalQuestions();

        }
    );


    // =====================================================
    // Khi bấm New
    // =====================================================
    $(document).on(
        "click",
        ".has-many-add",
        function () {

            setTimeout(function () {

                var selects = $("select[name*=\"[taxonomyitem_id]\"]");

                var newSelect = selects.last();

                // Xóa giá trị clone
                newSelect.val(null);

                newSelect.trigger("change");

                // Cập nhật danh mục
                updateTaxonomyOptions();

                // Tính lại tổng
                updateTotalQuestions();

            }, 100);

        }
    );


    // =====================================================
    // Khi xóa dòng
    // =====================================================
    $(document).on(
        "click",
        ".has-many-remove",
        function () {

            setTimeout(function () {

                updateTaxonomyOptions();

                updateTotalQuestions();

            }, 100);

        }
    );


    // =====================================================
    // Khi mở form
    // =====================================================
    $(document).ready(function () {

        setTimeout(function () {

            updateTaxonomyOptions();

            updateTotalQuestions();

        }, 300);

    });


})(jQuery);
</script>
HTML
        );

        return $form;
    }
}
