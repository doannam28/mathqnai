<?php

namespace App\Admin\Controllers;

use App\Models\Exams;
use App\Models\ExamQuestion;
use App\Models\Keys;
use App\Models\KeysTag;
use App\Models\Question;
use App\Models\TaxonomyItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Utility;
use Illuminate\Support\Facades\Storage;


class ExamsController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Từ khóa';

    protected function getQuestionLevelLabel($level): string
    {
        $labels = [
            0 => 'Dễ',
            1 => 'Trung Bình',
            2 => 'Khó',
        ];

        return $labels[(int) $level] ?? 'Khó';
    }

    protected function buildQuestionOptionHtml(array $questionsByCat, $catId, $selectedValue): string
    {
        $items = $questionsByCat[$catId] ?? [];
        $html = '<option value="">-- Chọn câu hỏi --</option>';

        foreach ($items as $item) {
            $selected = (string) $item['id'] === (string) $selectedValue ? ' selected' : '';
            $html .= '<option value="' . e($item['id']) . '"' . $selected . '>' . e($item['title']) . '</option>';
        }

        return $html;
    }

    protected function buildTitleOptionHtml(int $total, $selectedValue): string
    {
        $html = '<option value="">-- Chọn title --</option>';

        for ($i = 1; $i <= $total; $i++) {
            $selected = (string) $i === (string) $selectedValue ? ' selected' : '';
            $html .= '<option value="' . $i . '"' . $selected . '>Câu ' . $i . '</option>';
        }

        return $html;
    }

    protected function buildExamBuilderHtml(array $groups, array $questionsByCat, array $existingExamQuestionsByCat): string
    {
        if (empty($groups)) {
            return '<div class="text-muted">Mẫu đề này chưa có cấu hình số câu.</div>';
        }

        $totalQuestions = 0;
        foreach ($groups as $group) {
            $totalQuestions += (int) ($group['number'] ?? 0);
        }

        $html = '';
        foreach ($groups as $group) {
            $catId = (string) ($group['taxonomyitem_id'] ?? '');
            $existingRows = $existingExamQuestionsByCat[$catId] ?? [];
            $html .= '<div class="box box-default exam-question-group" data-cat-id="' . e($catId) . '" style="margin-bottom: 15px;">';
            $html .= '<div class="box-header with-border"><strong>' . e($group['taxonomy_name'] ?? 'Danh mục toán') . '</strong>';
            $html .= ' <span class="text-muted">(' . (int) ($group['number'] ?? 0) . ' câu)</span>';
            $html .= '<button type="button" class="btn btn-xs btn-primary pull-right auto-fill-group" data-cat-id="' . e($catId) . '">Tạo tự động</button>';
            $html .= '</div>';
            $html .= '<div class="box-body">';

            $total = (int) ($group['number'] ?? 0);
            for ($i = 0; $i < $total; $i++) {
                $existingRow = $existingRows[$i] ?? [];
                $selectedQuestionId = $existingRow['question_id'] ?? '';
                $selectedTitle = $existingRow['order'] ?? '';

                $html .= '<div class="form-group" style="margin-bottom: 12px;">';
                $html .= '<label>Câu ' . ($i + 1) . '</label>';
                $html .= '<select class="form-control question-select" data-cat-id="' . e($catId) . '" name="exam_questions[' . e($catId) . '][' . $i . '][question_id]" required>';
                $html .= $this->buildQuestionOptionHtml($questionsByCat, $catId, $selectedQuestionId);
                $html .= '</select>';
                $html .= '<div style="height: 8px;"></div>';
                $html .= '<select class="form-control title-select" name="exam_questions[' . e($catId) . '][' . $i . '][title]" required>';
                $html .= $this->buildTitleOptionHtml($totalQuestions, $selectedTitle);
                $html .= '</select>';
                $html .= '</div>';
            }

            $html .= '</div></div>';
        }

        return $html;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Exams());
        // Sắp xếp mặc định theo ID giảm dần
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('title', __('Tiêu đề'));
        $grid->column('keys_id', __('Thuộc mẫu đề'))->display(function () {
            $cat = Keys::where('id', $this->keys_id)->first();
            return isset($cat->title) ? $cat->title : '';
        });
        $grid->column('total_questions', __('Tổng số câu'))->display(function () {
            return ExamQuestion::where('exam_id', $this->id)->count();
        });
        $grid->column('question_detail', __('Chi tiết'))->display(function () {
            $rows = DB::table('exam_question as eq')
                ->join('questions as q', 'eq.question_id', '=', 'q.id')
                ->where('eq.exam_id', $this->id)
                ->select('q.cat_id', DB::raw('COUNT(*) as total'))
                ->groupBy('q.cat_id')
                ->orderBy('q.cat_id')
                ->get();

            if ($rows->isEmpty()) {
                return '';
            }

            $catIds = $rows->pluck('cat_id')->all();
            $catNames = TaxonomyItem::whereIn('id', $catIds)->pluck('name', 'id');
            $html = '';

            foreach ($rows as $row) {
                $name = $catNames[$row->cat_id] ?? ('Danh mục ' . $row->cat_id);
                $html .= '<div> - ' . e($name) . ': ' . (int) $row->total . ' câu</div>';
            }

            return $html;
        });
        $grid->column('status', __('Status'))->switch();
        $grid->column('test', __('Đề kiểm tra đầu vào'))->switch();
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
        $show = new Show(Exams::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Tiêu đề'));
        $show->field('status', __('Status'))->using([0 => 'Inactive', 1 => 'Active']);
        $show->field('test', __('Đề kiểm tra đầu vào'))->using([0 => 'Không', 1 => 'Có']);
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
        $cats = Keys::pluck('title', 'id');
        $keysConfig = Keys::with(['keyTags.taxonomyitem'])->get()->mapWithKeys(function ($key) {
            return [$key->id => $key->keyTags->map(function ($tag) {
                return [
                    'taxonomyitem_id' => (int) $tag->taxonomyitem_id,
                    'taxonomy_name' => $tag->taxonomyitem->name ?? '',
                    'number' => (int) $tag->number,
                ];
            })->values()->all()];
        })->toArray();

        $taxonomyIds = collect($keysConfig)
            ->flatten(1)
            ->pluck('taxonomyitem_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $questionsByCat = Question::query()
            ->select(['id', 'title', 'level', 'cat_id'])
            ->when(!empty($taxonomyIds), function ($query) use ($taxonomyIds) {
                $query->whereIn('cat_id', $taxonomyIds);
            })
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->groupBy('cat_id')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'id' => (int) $item->id,
                        'title' => $item->title . ' - ' . $this->getQuestionLevelLabel($item->level),
                    ];
                })->values()->all();
            })
            ->toArray();

        $form = new Form(new Exams());
        $editingExamId = (int) Request::segment(3);
        $editingExam = $editingExamId > 0 ? Exams::find($editingExamId) : null;
        $existingExamQuestionsByCat = [];
        $form->text('title', __('Tiêu đề'));
        $form->select('keys_id', __('Chọn mẫu đề'))
            ->options($cats)
            ->attribute('id', 'keys_id')
            ->required();

        if (!empty($editingExam)) {
            $existingExamQuestionsByCat = ExamQuestion::query()
                ->join('questions', 'exam_question.question_id', '=', 'questions.id')
                ->where('exam_question.exam_id', $editingExam->id)
                ->orderBy('questions.cat_id')
                ->orderBy('exam_question.id')
                ->get([
                    'exam_question.question_id',
                    'exam_question.title',
                    'exam_question.order',
                    'questions.cat_id',
                ])
                ->groupBy('cat_id')
                ->map(function ($item) {
                    return $item->map(function ($row) {
                        return [
                            'question_id' => (int) $row->question_id,
                            'title' => (string) $row->title,
                            'order' => (int) $row->order,
                        ];
                    })->values()->all();
                })
                ->all();
        }

        $keysConfigJson = json_encode($keysConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $questionsByCatJson = json_encode($questionsByCat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $existingExamQuestionsByCatJson = json_encode($existingExamQuestionsByCat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $initialBuilderHtml = '';
        if (!empty($editingExam)) {
            $selectedKeysId = (string) $editingExam->keys_id;
            $initialBuilderHtml = $this->buildExamBuilderHtml(
                $keysConfig[$selectedKeysId] ?? [],
                $questionsByCat,
                $existingExamQuestionsByCat
            );
        }

        $form->html(
            '<div id="exam-question-builder" class="exam-question-builder" style="margin-top: 20px;"'
            . ' data-keys-config="' . e($keysConfigJson) . '"'
            . ' data-questions-by-cat="' . e($questionsByCatJson) . '"'
            . ' data-existing-exam-questions-by-cat="' . e($existingExamQuestionsByCatJson) . '">'
            . $initialBuilderHtml
            . '</div>'
        );

        $examsCss = '/assets/css/admin-exams.css?v=' . filemtime(public_path('assets/css/admin-exams.css'));
        $examsJs = '/assets/js/admin-exams.js?v=' . filemtime(public_path('assets/js/admin-exams.js'));

        \Admin::css($examsCss);
        \Admin::js($examsJs);

        $form->hidden('test')->default(0);
        $form->switch('status', __('Status'))->default(1);

        $form->saved(function (Form $form) {
            $examId = $form->model()->id;
            $keysId = (int) $form->keys_id;
            // Chỉ đồng bộ exam_question khi submit từ form có builder câu hỏi.
            // Inline switch ở grid không gửi exam_questions, nên không được xóa dữ liệu cũ.
            if (!Request::exists('exam_questions')) {
                return;
            }
            $groups = KeysTag::where('keys_id', $keysId)->orderBy('id')->get();
            $inputQuestions = Request::input('exam_questions', []);
            $rows = [];
            $selectedQuestionIds = [];
            $selectedOrderNumbers = [];

            foreach ($groups as $group) {
                $groupQuestionRows = $inputQuestions[$group->taxonomyitem_id] ?? [];

                foreach ((array) $groupQuestionRows as $row) {
                    $questionId = (int) ($row['question_id'] ?? 0);
                    $titleOrder = (int) ($row['title'] ?? 0);

                    if (
                        $questionId > 0 &&
                        $titleOrder > 0 &&
                        !in_array($questionId, $selectedQuestionIds, true) &&
                        !in_array($titleOrder, $selectedOrderNumbers, true)
                    ) {
                        $selectedQuestionIds[] = $questionId;
                        $selectedOrderNumbers[] = $titleOrder;
                        $rows[] = [
                            'exam_id' => $examId,
                            'question_id' => $questionId,
                            'title' => 'Câu ' . $titleOrder,
                            'order' => $titleOrder,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            DB::table('exam_question')->where('exam_id', $examId)->delete();

            if (!empty($rows)) {
                DB::table('exam_question')->insert($rows);
            }
        });
        return $form;
    }
}
