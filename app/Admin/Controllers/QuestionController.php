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

    protected function getQuestionTypeLabel($type): string
    {
        return match ((int) $type) {
            1 => 'Nhiều đáp án',
            2 => 'Đúng/Sai',
            default => 'Một đáp án',
        };
    }

    protected function normalizeResultValues($result): array
    {
        if (is_array($result)) {
            return array_values(array_filter(array_map('trim', $result), static fn ($value) => $value !== ''));
        }

        $result = trim((string) $result);
        if ($result === '') {
            return [];
        }

        $decoded = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded), static fn ($value) => $value !== ''));
        }

        return array_values(array_filter(array_map('trim', explode(',', $result)), static fn ($value) => $value !== ''));
    }

    protected function buildAnswerOptionHtml(string $name, array $options, array $selectedValues, string $type = 'radio'): string
    {
        $html = '<div class="row g-2">';
        foreach ($options as $value => $label) {
            $id = $name . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $value);
            $checked = in_array((string) $value, $selectedValues, true) ? ' checked' : '';
            $inputName = $type === 'checkbox' ? $name . '[]' : $name;
            $inputType = $type === 'checkbox' ? 'checkbox' : 'radio';
            $html .= '<div class="col-md-6"><label class="question-answer-option">';
            $html .= '<input type="' . $inputType . '" name="' . e($inputName) . '" value="' . e((string) $value) . '" id="' . e($id) . '"' . $checked . '>';
            $html .= '<span>' . e($label) . '</span>';
            $html .= '</label></div>';
        }
        $html .= '</div>';

        return $html;
    }

    protected function buildAnswerEditorHtml(int $type, array $selectedValues): string
    {
        $singleOptions = [
            'A' => 'A',
            'B' => 'B',
            'C' => 'C',
            'D' => 'D',
        ];
        $trueFalseOptions = [
            'Đúng' => 'Đúng',
            'Sai' => 'Sai',
        ];

        $selectedSingle = [$selectedValues[0] ?? ''];
        $selectedMulti = $selectedValues;
        $selectedTrueFalse = [$selectedValues[0] ?? ''];

        return '
            <div class="question-answer-editor mt-3">
                <div class="question-answer-block" data-type="0">
                    <div class="fw-semibold mb-2">Đáp án một lựa chọn</div>
                    ' . $this->buildAnswerOptionHtml('result_single', $singleOptions, $selectedSingle, 'radio') . '
                </div>
                <div class="question-answer-block mt-3" data-type="1">
                    <div class="fw-semibold mb-2">Đáp án nhiều lựa chọn</div>
                    <div class="text-muted small mb-2">Có thể chọn nhiều phương án cùng lúc.</div>
                    ' . $this->buildAnswerOptionHtml('result_multi', $singleOptions, $selectedMulti, 'checkbox') . '
                </div>
                <div class="question-answer-block mt-3" data-type="2">
                    <div class="fw-semibold mb-2">Đáp án đúng / sai</div>
                    ' . $this->buildAnswerOptionHtml('result_tf', $trueFalseOptions, $selectedTrueFalse, 'radio') . '
                </div>
            </div>
        ';
    }

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
        $questionTypeLabels = [
            0 => 'Một đáp án',
            1 => 'Nhiều đáp án',
            2 => 'Đúng/Sai',
        ];
        $grid->column('type', __('Kiểu câu hỏi'))->display(function () use ($questionTypeLabels) {
            return $questionTypeLabels[(int) ($this->type ?? 0)] ?? 'Một đáp án';
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
        $typeOptions = [
            0 => 'Một đáp án (A, B, C, D)',
            1 => 'Nhiều đáp án (A, B, C, D)',
            2 => 'Đúng / Sai',
        ];
        $editingQuestionId = (int) Request::segment(3);
        $editingQuestion = $editingQuestionId > 0 ? Question::find($editingQuestionId) : null;
        $form->select('type', 'Kiểu câu hỏi')
            ->options($typeOptions)
            ->default(0)
            ->required();
        $form->select('cat_id', 'Danh mục toán')
            ->options(
                \App\Models\Taxonomyitem::where('taxonomy_id', 1)
                    ->pluck('name', 'id')
            )
            ->required();
        $form->tinyEditor('content', __('Câu hỏi'));
        $selectedResultValues = $this->normalizeResultValues($editingQuestion->result ?? '');
        $form->hidden('result')->attribute('id', 'question_result');
        $form->html($this->buildAnswerEditorHtml((int) ($editingQuestion->type ?? 0), $selectedResultValues));
        $form->number('order', __('Vị trí'))->default(0);
        $form->switch('status', __('Trạng thái'))->default(1);
        $form->saving(function (\Encore\Admin\Form $form) {
            $request = Request::all();
            $type = (int) ($request['type'] ?? $form->type ?? 0);
            $result = '';

            if ($type === 1) {
                $multi = $request['result_multi'] ?? [];
                $multi = is_array($multi) ? array_values(array_filter(array_map('trim', $multi), static fn ($value) => $value !== '')) : [];
                $result = implode(',', $multi);
            } elseif ($type === 2) {
                $result = trim((string) ($request['result_tf'] ?? ''));
            } else {
                $result = trim((string) ($request['result_single'] ?? ''));
            }

            if ($result === '') {
                throw new \RuntimeException('Vui lòng chọn câu trả lời cho câu hỏi.');
            }

            $form->result = $result;
            if(!isset($request["_edit_inline"]) && !empty($form->title)) {
                if(!empty($form->content)){
                    $content = $form->content;
                    $content = str_replace("../../../uploads","/uploads", $content);
                    $content = str_replace("../../uploads","/uploads", $content);
                    $form->content = str_replace("../uploads","/uploads", $content);
                }
            }
        });

        \Admin::script(<<<'JS'
$(function () {
    var typeSelect = $('select[name="type"]');
    var hiddenResult = $('#question_result');
    var blocks = $('.question-answer-block');

    function syncResultValue() {
        var type = parseInt(typeSelect.val() || '0', 10);
        blocks.hide();
        blocks.filter('[data-type="' + type + '"]').show();

        var value = '';
        if (type === 1) {
            var values = [];
            $('input[name="result_multi[]"]:checked').each(function () {
                values.push($(this).val());
            });
            value = values.join(',');
        } else if (type === 2) {
            value = $('input[name="result_tf"]:checked').val() || '';
        } else {
            value = $('input[name="result_single"]:checked').val() || '';
        }

        hiddenResult.val(value);
    }

    $(document).on('change', 'input[name="result_single"], input[name="result_multi[]"], input[name="result_tf"], select[name="type"]', syncResultValue);

    typeSelect.on('change', syncResultValue);
    syncResultValue();
});
JS);
        return $form;
    }
}
