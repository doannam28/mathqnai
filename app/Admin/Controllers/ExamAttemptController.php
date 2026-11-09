<?php

namespace App\Admin\Controllers;

use App\Models\ExamAttempt;
use App\Models\ExamResult;
use App\Models\Exams;
use App\Models\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ExamAttemptController extends BaseAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Lịch sử làm bài';

    protected function attemptTypeLabel(int $type): string
    {
        return match ($type) {
            0 => 'Kiểm tra đầu vào',
            1 => 'Luyện tập',
            2 => 'Thi thử',
            default => 'Bài làm',
        };
    }

    protected function attemptStatusMeta(int $status): array
    {
        return match ($status) {
            1 => ['text' => 'Hoàn thành', 'class' => 'success'],
            2 => ['text' => 'Hết giờ', 'class' => 'warning'],
            default => ['text' => 'Chưa hoàn thành', 'class' => 'default'],
        };
    }

    protected function formatAnswerValue($value): string
    {
        if ($value === null) {
            return '-';
        }

        $value = trim((string) $value);
        if ($value === '') {
            return '-';
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $decoded = array_values(array_filter(array_map(static fn ($item) => trim((string) $item), $decoded), static fn ($item) => $item !== ''));

            if (!empty($decoded)) {
                return implode(', ', $decoded);
            }
        }

        return $value;
    }

    public function buildResultsTable(ExamAttempt $attempt): string
    {
        $rows = ExamResult::query()
            ->from('exam_results as er')
            ->leftJoin('questions as q', 'er.question_id', '=', 'q.id')
            ->where('er.attempt_id', $attempt->id)
            ->orderBy('er.id')
            ->get([
                'er.id',
                'er.question_id',
                'er.answer',
                'er.correct_answer',
                'er.is_correct',
                'q.title as question_title',
                'q.content as question_content',
            ]);

        if ($rows->isEmpty()) {
            return '<div class="text-muted">Chưa có dữ liệu chi tiết cho lần làm bài này.</div>';
        }

        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-striped table-hover">';
        $html .= '<thead><tr>';
        $html .= '<th style="width: 70px;">#</th>';
        $html .= '<th>Câu hỏi</th>';
        $html .= '<th>Đáp án của học sinh</th>';
        $html .= '<th>Đáp án đúng</th>';
        $html .= '<th style="width: 120px;">Kết quả</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $index => $row) {
            $isCorrect = (bool) $row->is_correct;
            $badgeClass = $isCorrect ? 'success' : 'danger';
            $title = trim((string) ($row->question_title ?? ''));
            if ($title === '') {
                $title = 'Câu hỏi #' . (int) ($row->question_id ?? 0);
            }

            $content = trim(strip_tags((string) ($row->question_content ?? '')));
            $content = $content !== '' ? Str::limit($content, 140) : '';

            $html .= '<tr>';
            $html .= '<td>' . ($index + 1) . '</td>';
            $html .= '<td>';
            $html .= '<div class="fw-semibold">' . e($title) . '</div>';
            if ($content !== '') {
                $html .= '<div class="text-muted small mt-1">' . e($content) . '</div>';
            }
            $html .= '</td>';
            $html .= '<td>' . e($this->formatAnswerValue($row->answer)) . '</td>';
            $html .= '<td>' . e($this->formatAnswerValue($row->correct_answer)) . '</td>';
            $html .= '<td><span class="label label-' . $badgeClass . '">' . ($isCorrect ? 'Đúng' : 'Sai') . '</span></td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $typeLabels = [
            0 => 'Kiểm tra đầu vào',
            1 => 'Luyện tập',
            2 => 'Thi thử',
        ];

        $statusMeta = [
            0 => ['text' => 'Chưa hoàn thành', 'class' => 'default'],
            1 => ['text' => 'Hoàn thành', 'class' => 'success'],
            2 => ['text' => 'Hết giờ', 'class' => 'warning'],
        ];

        $grid = new Grid(new ExamAttempt());
        $grid->model()
            ->with(['user', 'exam'])
            ->orderByDesc('id');

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();

            $filter->equal('user_id', 'Học sinh')->select(
                User::query()
                    ->orderBy('name')
                    ->get(['id', 'name', 'email'])
                    ->mapWithKeys(function ($user) {
                        $label = trim((string) $user->name);
                        $label .= $label !== '' ? ' - ' : '';
                        $label .= $user->email ?: ('#' . $user->id);

                        return [$user->id => $label];
                    })
                    ->toArray()
            );

            $filter->equal('type', 'Loại bài')->select([
                0 => 'Kiểm tra đầu vào',
                1 => 'Luyện tập',
                2 => 'Thi thử',
            ]);

            $filter->equal('status', 'Trạng thái')->select([
                0 => 'Chưa hoàn thành',
                1 => 'Hoàn thành',
                2 => 'Hết giờ',
            ]);

            $filter->equal('exam_id', 'Đề')->select(
                Exams::query()
                    ->orderByDesc('id')
                    ->get(['id', 'title'])
                    ->mapWithKeys(function ($exam) {
                        $label = trim((string) $exam->title);
                        if ($label === '') {
                            $label = 'Đề #' . $exam->id;
                        }

                        return [$exam->id => $label];
                    })
                    ->toArray()
            );

            $filter->between('created_at', 'Ngày làm bài')->datetime();
            $filter->like('topic_title', 'Tiêu đề bài làm');
        });

        $grid->column('id', __('Id'));
        $grid->column('user_id', 'Học sinh')->display(function () {
            $user = $this->user;

            if (!$user) {
                return '<span class="text-muted">Không xác định</span>';
            }

            $name = trim((string) ($user->name ?? ''));
            $email = trim((string) ($user->email ?? ''));
            $label = $name !== '' ? $name : 'Học sinh #' . (int) $user->id;

            if ($email !== '') {
                $label .= '<div class="text-muted small">' . e($email) . '</div>';
            }

            return $label;
        });
        $grid->column('type', 'Loại bài')->display(function () {
            $typeLabels = [
                0 => 'Kiểm tra đầu vào',
                1 => 'Luyện tập',
                2 => 'Thi thử',
            ];

            return $typeLabels[(int) ($this->type ?? 0)] ?? 'Bài làm';
        });
        $grid->column('topic_title', 'Tên bài')->limit(60);
        $grid->column('exam_id', 'Đề')->display(function () {
            if ($this->exam) {
                $title = trim((string) ($this->exam->title ?? ''));
                return $title !== '' ? $title : ('Đề #' . (int) $this->exam->id);
            }

            return $this->topic_title ?: '<span class="text-muted">Không áp dụng</span>';
        });
        $grid->column('total_questions', 'Tổng câu');
        $grid->column('correct_count', 'Đúng');
        $grid->column('wrong_count', 'Sai');
        $grid->column('score', 'Điểm');
        $grid->column('status', 'Trạng thái')->display(function () {
            $statusMeta = [
                0 => ['text' => 'Chưa hoàn thành', 'class' => 'default'],
                1 => ['text' => 'Hoàn thành', 'class' => 'success'],
                2 => ['text' => 'Hết giờ', 'class' => 'warning'],
            ];
            $meta = $statusMeta[(int) ($this->status ?? 0)] ?? $statusMeta[0];

            return '<span class="label label-' . $meta['class'] . '">' . e($meta['text']) . '</span>';
        });
        $grid->column('spent_seconds', 'Thời gian làm')->display(function ($seconds) {
            $seconds = (int) $seconds;
            if ($seconds <= 0) {
                return '<span class="text-muted">-</span>';
            }

            $minutes = (int) floor($seconds / 60);
            $remainingSeconds = $seconds % 60;

            return sprintf('%d phút %02d giây', $minutes, $remainingSeconds);
        });
        $grid->column('started_at', 'Bắt đầu')->display(function ($value) {
            return $value ? date('d/m/Y H:i', strtotime((string) $value)) : '<span class="text-muted">-</span>';
        });
        $grid->column('submitted_at', 'Nộp bài')->display(function ($value) {
            return $value ? date('d/m/Y H:i', strtotime((string) $value)) : '<span class="text-muted">-</span>';
        });
        $grid->column('created_at', 'Ngày tạo')->display(function ($value) {
            return $value ? date('d/m/Y H:i', strtotime((string) $value)) : '<span class="text-muted">-</span>';
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->disableCreateButton();

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
        $typeLabels = [
            0 => 'Kiểm tra đầu vào',
            1 => 'Luyện tập',
            2 => 'Thi thử',
        ];
        $statusMeta = [
            0 => ['text' => 'Chưa hoàn thành', 'class' => 'default'],
            1 => ['text' => 'Hoàn thành', 'class' => 'success'],
            2 => ['text' => 'Hết giờ', 'class' => 'warning'],
        ];

        $attempt = ExamAttempt::with(['user', 'exam'])->findOrFail($id);
        $controller = $this;
        $show = new Show($attempt);

        $show->field('id', __('Id'));
        $show->field('user_id', 'Học sinh')->as(function () use ($attempt) {
            if (!$attempt->user) {
                return 'Không xác định';
            }

            $name = trim((string) ($attempt->user->name ?? ''));
            $email = trim((string) ($attempt->user->email ?? ''));

            if ($email !== '') {
                return $name !== '' ? ($name . ' - ' . $email) : $email;
            }

            return $name !== '' ? $name : ('Học sinh #' . (int) $attempt->user->id);
        });
        $show->field('type', 'Loại bài')->as(function () use ($attempt) {
            $typeLabels = [
                0 => 'Kiểm tra đầu vào',
                1 => 'Luyện tập',
                2 => 'Thi thử',
            ];

            return $typeLabels[(int) ($attempt->type ?? 0)] ?? 'Bài làm';
        });
        $show->field('topic_title', 'Tên bài');
        $show->field('exam_id', 'Đề')->as(function () use ($attempt) {
            if ($attempt->exam) {
                $title = trim((string) ($attempt->exam->title ?? ''));
                return $title !== '' ? $title : ('Đề #' . (int) $attempt->exam->id);
            }

            return $attempt->topic_title ?: 'Không áp dụng';
        });
        $show->field('total_questions', 'Tổng câu');
        $show->field('correct_count', 'Đúng');
        $show->field('wrong_count', 'Sai');
        $show->field('score', 'Điểm');
        $show->field('time_limit', 'Giới hạn thời gian')->as(function () use ($attempt) {
            return (int) $attempt->time_limit > 0 ? ((int) $attempt->time_limit . ' phút') : 'Không giới hạn';
        });
        $show->field('spent_seconds', 'Thời gian đã làm')->as(function () use ($attempt) {
            $seconds = (int) $attempt->spent_seconds;
            if ($seconds <= 0) {
                return '-';
            }

            $minutes = (int) floor($seconds / 60);
            $remainingSeconds = $seconds % 60;

            return sprintf('%d phút %02d giây', $minutes, $remainingSeconds);
        });
        $show->field('status', 'Trạng thái')->as(function () use ($attempt) {
            $statusMeta = [
                0 => ['text' => 'Chưa hoàn thành', 'class' => 'default'],
                1 => ['text' => 'Hoàn thành', 'class' => 'success'],
                2 => ['text' => 'Hết giờ', 'class' => 'warning'],
            ];
            $meta = $statusMeta[(int) ($attempt->status ?? 0)] ?? $statusMeta[0];

            return $meta['text'];
        });
        $show->field('started_at', 'Bắt đầu')->as(function () use ($attempt) {
            return $attempt->started_at ? $attempt->started_at->format('d/m/Y H:i') : '-';
        });
        $show->field('submitted_at', 'Nộp bài')->as(function () use ($attempt) {
            return $attempt->submitted_at ? $attempt->submitted_at->format('d/m/Y H:i') : '-';
        });
        $show->field('created_at', 'Ngày tạo')->as(function () use ($attempt) {
            return $attempt->created_at ? $attempt->created_at->format('d/m/Y H:i') : '-';
        });
        $show->field('updated_at', 'Cập nhật')->as(function () use ($attempt) {
            return $attempt->updated_at ? $attempt->updated_at->format('d/m/Y H:i') : '-';
        });
        $show->field('question_results', 'Chi tiết câu trả lời')->unescape()->as(function () use ($attempt, $controller) {
            return $controller->buildResultsTable($attempt);
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ExamAttempt());

        $form->html('<div class="alert alert-info mb-0">Trang này chỉ dùng để xem lịch sử làm bài. Vui lòng mở danh sách hoặc chi tiết để tra cứu dữ liệu.</div>');
        $form->disableSubmit();
        $form->disableReset();
        $form->tools(function ($tools) {
            $tools->disableView();
            $tools->disableDelete();
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}
