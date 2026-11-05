<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ExamAttempt;
use App\Models\ExamResult;
use App\Models\ExamQuestion;
use App\Models\Exams;
use App\Models\Keys;
use App\Models\Number;
use App\Models\Question;
use App\Models\Post;
use App\Models\Settings;
use App\Models\TaxonomyItem;
use App\Models\Tag;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class HomeController extends Controller
{
    protected function formatDateVietnamese($dateInput) {
        // Chuyển thành timestamp
        $timestamp = strtotime($dateInput);

        // Mảng ngày trong tuần tiếng Việt
        $days = [
            'Chủ nhật',
            'Thứ hai',
            'Thứ ba',
            'Thứ tư',
            'Thứ năm',
            'Thứ sáu',
            'Thứ bảy'
        ];

        // Lấy thứ trong tuần (0 = CN, 1 = Thứ 2, ...)
        $dayOfWeek = date('w', $timestamp);

        // Định dạng ngày theo dd/mm/YYYY
        $formattedDate = date('d/m/Y', $timestamp);

        return $days[$dayOfWeek] . ', Ngày ' . $formattedDate;
    }

    protected function buildAttemptTopicTitle(int $attemptType, string $examTitle): string
    {
        $examTitle = trim($examTitle);

        return match ($attemptType) {
            1 => 'Luyện tập - ' . $examTitle,
            2 => 'Thi thử - ' . $examTitle,
            default => $examTitle !== '' ? $examTitle : 'Bài làm',
        };
    }

    protected function getPracticeLevelMeta(int $level): array
    {
        return match ($level) {
            1 => ['label' => 'Dễ', 'badge' => 'primary', 'color' => '#2563eb'],
            2 => ['label' => 'Trung Bình', 'badge' => 'warning', 'color' => '#f59e0b'],
            3 => ['label' => 'Khó', 'badge' => 'danger', 'color' => '#dc2626'],
            default => ['label' => 'Dễ', 'badge' => 'primary', 'color' => '#2563eb'],
        };
    }

    protected function getPracticeLevelByScore(float $score): int
    {
        if ($score < 5) {
            return 1;
        }

        if ($score <= 7) {
            return 2;
        }

        return 3;
    }

    protected function buildPracticeSessionKey(int $userId, int $taxonomyItemId, int $level): string
    {
        return 'practice_' . $userId . '_' . $taxonomyItemId . '_' . $level;
    }

    protected function buildPracticeTopicTitle(?string $taxonomyName, int $practiceLevelParam): string
    {
        $taxonomyName = trim((string) $taxonomyName) !== '' ? trim((string) $taxonomyName) : 'Dạng toán';
        $levelLabel = match ($practiceLevelParam) {
            1 => 'dễ',
            2 => 'trung bình',
            3 => 'khó',
            default => 'Random',
        };

        return 'Luyện tập - ' . $taxonomyName . ' - Câu hỏi ' . $levelLabel;
    }

    protected function getPracticeLevelFromTitle(?string $topicTitle): int
    {
        $title = mb_strtolower(trim((string) $topicTitle));

        if (str_contains($title, 'câu hỏi dễ')) {
            return 1;
        }

        if (str_contains($title, 'câu hỏi trung bình')) {
            return 2;
        }

        if (str_contains($title, 'câu hỏi khó')) {
            return 3;
        }

        return 0;
    }

    /**
     * @return Factory|View|Application
     */
    public function index()
    {
        $user = auth()->user();
        $setting = Settings::first();

        $completedAttempts = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->where('total_questions', '>', 0)
            ->whereNotNull('exam_id')
            ->get();

        $currentScore = 0.0;
        if ($completedAttempts->count() > 0) {
            $currentScore = (float) round($completedAttempts->avg(function ($attempt) {
                $totalQuestions = (int) ($attempt->total_questions ?? 0);
                if ($totalQuestions <= 0) {
                    return 0;
                }

                return (($attempt->score ?? 0) / $totalQuestions) * 10;
            }), 1);
        }

        $targetScore = (float) ($user->target_score ?? 0);
        if ($targetScore <= 0) {
            $targetScore = (float) ($setting->target_score ?? 8.0);
        }

        $attentionTopicRows = DB::table('exam_results as er')
            ->join('exam_attempts as ea', 'er.attempt_id', '=', 'ea.id')
            ->join('questions as q', 'er.question_id', '=', 'q.id')
            ->where('ea.user_id', $user->id)
            ->where('ea.status', 1)
            ->whereNotNull('ea.exam_id')
            ->select('q.cat_id', DB::raw('AVG(CASE WHEN er.is_correct = 1 THEN 1 ELSE 0 END) * 10 as average_score'))
            ->groupBy('q.cat_id')
            ->get();

        $attentionTopics = [];
        $currentScoreTopic = null;
        $knowledgeBreakdown = [];
        $knowledgeChartLabels = [];
        $knowledgeChartValues = [];
        $knowledgeChartColors = [];
        $recommendedPracticeTopic = null;
        if ($attentionTopicRows->count() > 0) {
            $catIds = $attentionTopicRows->pluck('cat_id')->filter()->unique()->values()->all();
            $catRows = TaxonomyItem::whereIn('id', $catIds)->get(['id', 'name', 'icon_name', 'color'])->keyBy('id');
            $attentionTopics = $attentionTopicRows
                ->map(function ($row) use ($catRows) {
                    $score = (float) round((float) ($row->average_score ?? 0), 1);
                    $cat = $catRows[$row->cat_id] ?? null;
                    return [
                        'id' => (int) $row->cat_id,
                        'name' => $cat->name ?? ('Danh mục ' . $row->cat_id),
                        'score' => $score,
                        'class' => $score < 4 ? 'danger' : 'warning',
                        'icon_name' => $cat->icon_name ?? 'calculate',
                        'color' => $cat->color ?? '#2563eb',
                    ];
                })
                ->filter(fn ($topic) => $topic['score'] < 6)
                ->sortBy('score')
                ->values()
                ->all();

            $knowledgeBreakdown = $attentionTopicRows
                ->map(function ($row) use ($catRows) {
                    $score = (float) round((float) ($row->average_score ?? 0), 1);
                    $cat = $catRows[$row->cat_id] ?? null;
                    return [
                        'id' => (int) $row->cat_id,
                        'name' => $cat->name ?? ('Danh mục ' . $row->cat_id),
                        'score' => $score,
                        'status' => $score >= 8 ? 'Rất tốt' : ($score >= 6 ? 'Khá' : ($score >= 4 ? 'Trung bình' : 'Cần cải thiện')),
                        'class' => $score >= 8 ? 'good' : ($score >= 6 ? 'good' : ($score >= 4 ? 'average' : 'improve')),
                        'color' => $cat->color ?? '#2563eb',
                        'icon_name' => $cat->icon_name ?? 'calculate',
                        'topic_color' => $cat->color ?? '#2563eb',
                    ];
                })
                ->sortByDesc('score')
                ->values()
                ->all();

            $currentScoreTopic = $knowledgeBreakdown[0] ?? ($attentionTopics[0] ?? null);
            $recommendedPracticeTopic = collect($knowledgeBreakdown)
                ->sortBy('score')
                ->first();

            $knowledgeChartLabels = array_map(static fn ($topic) => $topic['name'], $knowledgeBreakdown);
            $knowledgeChartValues = array_map(static fn ($topic) => $topic['score'], $knowledgeBreakdown);
            $knowledgeChartColors = array_map(static fn ($topic) => $topic['topic_color'] ?? $topic['color'] ?? '#2563eb', $knowledgeBreakdown);
        }

        $recommendedPracticeLevel = 0;
        $recommendedPracticeUrl = url('/luyen-tap');
        if (!empty($recommendedPracticeTopic) && isset($recommendedPracticeTopic['score'])) {
            $recommendedPracticeLevel = $this->getPracticeLevelByScore((float) $recommendedPracticeTopic['score']);
            $recommendedPracticeUrl = url('/luyen-tap/' . (int) ($recommendedPracticeTopic['id'] ?? 0) . '/' . $recommendedPracticeLevel);
        }

        $progressAttempts = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->where('type', 2)
            ->where('total_questions', '>', 0)
            ->whereNotNull('exam_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['score', 'total_questions', 'created_at']);

        $progressAttempts = $progressAttempts->reverse()->values();

        $progressChartLabels = [];
        $progressChartValues = [];
        foreach ($progressAttempts as $index => $attempt) {
            $dateLabel = !empty($attempt->created_at)
                ? date('d/m', strtotime((string) $attempt->created_at))
                : '';
            $progressChartLabels[] = $dateLabel !== ''
                ? 'Lần ' . ($index + 1) . ' (' . $dateLabel . ')'
                : 'Lần ' . ($index + 1);
            $progressChartValues[] = (float) round((($attempt->score ?? 0) / max(1, (int) ($attempt->total_questions ?? 0))) * 10, 1);
        }

        $currentScoreLabel = 'Chưa có dữ liệu';
        if ($currentScore >= 8) {
            $currentScoreLabel = 'Rất tốt';
        } elseif ($currentScore >= 6) {
            $currentScoreLabel = 'Mức khá';
        } elseif ($currentScore >= 4) {
            $currentScoreLabel = 'Mức trung bình';
        } elseif ($currentScore > 0) {
            $currentScoreLabel = 'Cần cố gắng';
        }

        return view('homes.index', [
            'currentScore' => $currentScore,
            'currentScoreLabel' => $currentScoreLabel,
            'targetScore' => $targetScore,
            'targetScoreMessage' => $currentScore >= $targetScore ? 'Bạn đã đạt mục tiêu!' : 'Cố gắng lên nhé!',
            'attentionTopics' => $attentionTopics,
            'currentScoreTopic' => $currentScoreTopic,
            'knowledgeBreakdown' => $knowledgeBreakdown,
            'knowledgeChartLabels' => $knowledgeChartLabels,
            'knowledgeChartValues' => $knowledgeChartValues,
            'knowledgeChartColors' => $knowledgeChartColors,
            'progressChartLabels' => $progressChartLabels,
            'progressChartValues' => $progressChartValues,
            'recommendedPracticeTopic' => $recommendedPracticeTopic,
            'recommendedPracticeLevel' => $recommendedPracticeLevel,
            'recommendedPracticeUrl' => $recommendedPracticeUrl,
        ]);
    }

    protected function normalizeSubmittedAnswer($answer): array|string
    {
        if (is_array($answer)) {
            return array_values(array_filter(array_map('trim', $answer), static fn ($value) => $value !== ''));
        }

        $answer = trim((string) $answer);
        if ($answer === '') {
            return '';
        }

        $decoded = json_decode($answer, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values(array_filter(array_map('trim', $decoded), static fn ($value) => $value !== ''));
        }

        if (str_contains($answer, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $answer)), static fn ($value) => $value !== ''));
        }

        return $answer;
    }

    protected function normalizeComparisonAnswer($answer): array|string
    {
        $normalized = $this->normalizeSubmittedAnswer($answer);

        if (is_array($normalized)) {
            $normalized = array_map(static fn ($value) => mb_strtoupper(trim((string) $value)), $normalized);
            sort($normalized);
            return array_values(array_unique($normalized));
        }

        return mb_strtoupper(trim((string) $normalized));
    }

    protected function answersMatch($type, $correctAnswer, $submittedAnswer): bool
    {
        $type = (int) $type;
        $correct = $this->normalizeComparisonAnswer($correctAnswer);
        $submitted = $this->normalizeComparisonAnswer($submittedAnswer);

        if ($type === 1) {
            return is_array($correct) && is_array($submitted) && $correct === $submitted;
        }

        return (string) $correct === (string) $submitted;
    }

    protected function stringifyAnswer($answer): string
    {
        $normalized = $this->normalizeSubmittedAnswer($answer);

        if (is_array($normalized)) {
            return json_encode(array_values($normalized), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $normalized;
    }
    public function sendMail()
    {
        try {
            $setting = Settings::first();
            $input = Request::all();
            foreach ($input as $k => $row) {
                $input[$k] = trim(strip_tags($row));
            }
            if (empty($input['email']) || !isset($input['title']) || !isset($input['phone']) || empty($input['content'])) {
                return Response::json(['status' => 'error', 'msg' => 'Xin vui lòng điền đẩy đủ thông tin!'], 201);
            }
            Mail::send('mail.contact', ['data' => $input], function ($m) use ($setting) {
                $m->to($setting->email_receive)->subject("Có khách liên hệ");
            });
            return Response::json([
                'success' => true,
                'message' => 'Gửi email thành công. Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất. Trân trọng!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function category($slug='')
    {
        $cat = Category::where('slug', $slug)->firstOrFail();
        $posts = Post::where('parent_id',$cat->id)->where('status',1)->orderBy('position','ASC')->orderBy('updated_at','DESC')->paginate(10);
        return view('homes.category', [
            'cat' => $cat,
            'posts' => $posts,
        ]);
    }
    public function tag($slug='')
    {
        $cat = Tag::where('slug', $slug)->firstOrFail();

        $posts = $cat->posts()->where('status',1)->with('category')
            ->orderBy('position', 'ASC')
            ->orderBy('updated_at', 'DESC')
            ->paginate(10); // phân trang 10 bài / trang
        return view('homes.tag', [
            'cat' => $cat,
            'posts' => $posts,
        ]);
    }
    public function page($slug='')
    {
        $cat = Question::where('slug', $slug)->firstOrFail();
        return view('homes.page', [
            'cat' => $cat,
        ]);
    }
    public function noiquy()
    {
        $title = 'Nội quy và điều khoản';
        return view('homes.noiquy', [
            'title' => $title,
        ]);
    }
    public function detail($slug='',$slug1='')
    {
        $post = Post::where('slug', $slug1)->with('tags')->firstOrFail();
        if($slug=='bai-viet'){
            $cat = Category::where('id', $post->parent_id)->firstOrFail();
        }else{
            $cat = Category::where('slug', $slug)->firstOrFail();
        }
        $posts = Post::where('parent_id',$post->parent_id)->where('id','<>',$post->id)->where('status',1)->orderBy('position','ASC')->orderBy('updated_at','DESC')->limit(5)->get();
        return view('homes.detail', [
            'cat' => $cat,
            'post' => $post,
            'posts' => $posts,
        ]);
    }
    public function ketqua()
    {
        $request = Request::all();
        //KET QUA XO SO
        //Thong ke dau
        $mang_dau=array();
        $obj = new Number();
        $cat_id = 13;
        if(isset($request['date'])){
            $date = $request['date'];
        }else{
            $obj2 = $obj->where('cat_id',intval($cat_id))->orderBy('date','desc')->first();
            $date = isset($obj2->date) ? $obj2->date : date('Y-m-d');
        }
        $kyhieu =new Keys();
        $kyhieu = $kyhieu->where('cat_id',intval($cat_id))->where('date',$date)->first();
        $obj1 = $obj->where('cat_id',intval($cat_id))->where('date',$date)->orderBy('giai','asc')->get();
        $mang=array();
        foreach ($obj1 as $row){
            $mang[intval($row->giai)][]=$row->number;
        }
        $dau = $obj->where('cat_id',intval($cat_id))->where('date',$date)->orderBy('duoi','asc')->get();

        foreach ($dau as $row){
            $mang_dau[intval(substr($row['duoi'],0,1))][]=$row->duoi;
        }

        //Thong ke  duoi
        $mang_duoi=array();
        foreach ($dau as $row){
            $mang_duoi[intval(substr($row['duoi'],1,1))][]=$row->duoi;
        }
        $title = 'KẾT QUẢ XỔ SỐ Miền Bắc (Hà Nội) NGÀY '.date('d-m-Y',strtotime($date));
        $list_date = Number::where('cat_id',intval($cat_id))->where('giai',0)->orderBy('date','DESC')->get();
        return view('homes.ketqua', [
            'title' => $title,
            'mang' => $mang,
            'kyhieu' => $kyhieu,
            'dau' => $mang_dau,
            'duoi' => $mang_duoi,
            'list_date' => $list_date,
        ]);
    }
    public function kiem_tra_dau_vao()
    {
        $setting = Settings::first();
        $timeLimit = (int) ($setting->time_test ?? 30);
        $exam = Exams::query()
            ->where('status', 1)
            ->where('test', 1)
            ->orderBy('id', 'asc')
            ->first();

        $questions = collect();
        $totalQuestions = 0;
        if ($exam) {
            $totalQuestions = ExamQuestion::where('exam_id', $exam->id)->count();
            $questions = DB::table('exam_question as eq')
                ->join('questions as q', 'eq.question_id', '=', 'q.id')
                ->where('eq.exam_id', $exam->id)
                ->orderBy('eq.order', 'asc')
                ->orderBy('eq.id', 'asc')
                ->get([
                    'q.id',
                    'q.title',
                    'q.content',
                    'q.result',
                    'q.type',
                    'eq.order as exam_order',
                    'eq.title as exam_title',
                ]);
        }

        return view('homes.kiem_tra_dau_vao', [
            'title' => 'Kiểm tra đầu vào',
            'examId' => $exam->id ?? 0,
            'attemptType' => 0,
            'taxonomyItemId' => 0,
            'timeLimit' => $timeLimit,
            'totalQuestions' => $totalQuestions,
            'questions' => $questions,
        ]);
    }

    public function thi_thu()
    {
        $setting = Settings::first();
        $timeLimit = (int) ($setting->time_exam ?? 45);
        $sessionKey = 'thi_thu_exam_id';
        $examId = (int) session($sessionKey, 0);
        $exam = null;

        if ($examId > 0) {
            $exam = Exams::query()
                ->where('id', $examId)
                ->where('status', 1)
                ->where('test', 0)
                ->first();
        }

        if (empty($exam)) {
            $exam = Exams::query()
                ->where('status', 1)
                ->where('test', 0)
                ->inRandomOrder()
                ->first();

            if ($exam) {
                session([$sessionKey => $exam->id]);
            } else {
                session()->forget($sessionKey);
            }
        }

        $questions = collect();
        $totalQuestions = 0;
        if ($exam) {
            $totalQuestions = ExamQuestion::where('exam_id', $exam->id)->count();
            $questions = DB::table('exam_question as eq')
                ->join('questions as q', 'eq.question_id', '=', 'q.id')
                ->where('eq.exam_id', $exam->id)
                ->orderBy('eq.order', 'asc')
                ->orderBy('eq.id', 'asc')
                ->get([
                    'q.id',
                    'q.title',
                    'q.content',
                    'q.result',
                    'q.type',
                    'eq.order as exam_order',
                    'eq.title as exam_title',
                ]);
        }

        return view('homes.thi_thu', [
            'title' => 'Thi thử',
            'examId' => $exam->id ?? 0,
            'attemptType' => 2,
            'taxonomyItemId' => 0,
            'timeLimit' => $timeLimit,
            'totalQuestions' => $totalQuestions,
            'questions' => $questions,
            'examTitle' => $exam->title ?? '',
        ]);
    }

    public function luyen_tap($taxonomyItemId = null, $level = null)
    {
        $topics = TaxonomyItem::query()
            ->where('taxonomy_id', 1)
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', 1);
            })
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'name', 'icon_name', 'color']);

        $selectedLevel = (int) Request::query('level', 0);
        if ($level !== null && $level !== '') {
            $selectedLevel = (int) $level;
        }
        if ($selectedLevel < 0 || $selectedLevel > 3) {
            $selectedLevel = 0;
        }

        $topicIds = $topics->pluck('id')->all();
        $topicQuestionCounts = [];
        if (!empty($topicIds)) {
            $countQuery = Question::query()
                ->where('status', 1)
                ->whereIn('cat_id', $topicIds);
            if ($selectedLevel > 0) {
                $countQuery->where('level', $selectedLevel - 1);
            }

            $topicQuestionCounts = $countQuery
                ->select('cat_id', DB::raw('COUNT(*) as total'))
                ->groupBy('cat_id')
                ->pluck('total', 'cat_id')
                ->toArray();
        }

        if (empty($taxonomyItemId) || $level === null || $level === '') {
            return view('homes.luyen_tap', [
                'title' => 'Luyện tập',
                'topics' => $topics,
                'selectedLevel' => $selectedLevel,
                'selectedLevelMeta' => $this->getPracticeLevelMeta($selectedLevel),
                'topicQuestionCounts' => $topicQuestionCounts,
            ]);
        }

        $taxonomyItemId = (int) $taxonomyItemId;
        $practiceLevelParam = (int) $level;
        if ($practiceLevelParam < 0 || $practiceLevelParam > 3) {
            return redirect('/luyen-tap');
        }

        $practiceLevel = $practiceLevelParam === 0 ? -1 : ($practiceLevelParam - 1);
        $taxonomy = TaxonomyItem::query()
            ->where('taxonomy_id', 1)
            ->findOrFail($taxonomyItemId);

        $practiceLevelMeta = $practiceLevelParam === 0
            ? ['label' => 'Random (Ngẫu nhiên)', 'badge' => 'secondary', 'color' => '#64748b']
            : $this->getPracticeLevelMeta($practiceLevelParam);
        $practiceTopicTitle = $this->buildPracticeTopicTitle($taxonomy->name ?? 'Dạng toán', $practiceLevelParam);
        $sessionKey = $this->buildPracticeSessionKey((int) auth()->id(), $taxonomyItemId, $practiceLevel);
        $practiceSession = session($sessionKey, []);
        $questionIds = $practiceSession['question_ids'] ?? [];
        $attemptId = (int) ($practiceSession['attempt_id'] ?? 0);

        if (empty($questionIds)) {
            if ($attemptId > 0) {
                $savedAttempt = ExamAttempt::query()
                    ->where('id', $attemptId)
                    ->where('user_id', auth()->id())
                    ->first();

                if (!empty($savedAttempt) && !empty($savedAttempt->question_ids)) {
                    $decodedQuestionIds = json_decode((string) $savedAttempt->question_ids, true);
                    if (is_array($decodedQuestionIds) && !empty($decodedQuestionIds)) {
                        $questionIds = array_values(array_filter($decodedQuestionIds, static fn ($value) => (int) $value > 0));
                    }
                }
            }

        }

        if (empty($questionIds)) {
            $questionQuery = Question::query()
                ->where('status', 1)
                ->where('cat_id', $taxonomyItemId);

            if ($practiceLevelParam > 0) {
                $questionQuery->where('level', $practiceLevel);
            }

            $questionIds = $questionQuery
                ->inRandomOrder()
                ->pluck('id')
                ->all();
        }

        if ($attemptId > 0) {
            $existingAttempt = ExamAttempt::query()
                ->where('id', $attemptId)
                ->where('user_id', auth()->id())
                ->first();
            if (empty($existingAttempt)) {
                $attemptId = 0;
            }
        }

        if ($attemptId <= 0) {
            $startedAt = now()->timestamp * 1000;
            $attempt = ExamAttempt::create([
                'exam_id' => null,
                'user_id' => auth()->id(),
                'type' => 1,
                'taxonomy_item_id' => $taxonomyItemId,
                'topic_title' => $practiceTopicTitle,
                'total_questions' => count($questionIds),
                'correct_count' => 0,
                'wrong_count' => 0,
                'score' => 0,
                'time_limit' => 0,
                'spent_seconds' => 0,
                'status' => 0,
                'started_at' => now(),
                'submitted_at' => null,
                'question_ids' => json_encode(array_values($questionIds), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $attemptId = $attempt->id;
        } else {
            $startedAt = (int) ($practiceSession['started_at'] ?? (now()->timestamp * 1000));
        }

        session([
            $sessionKey => [
                'attempt_id' => $attemptId,
                'question_ids' => $questionIds,
                'started_at' => $startedAt,
                'taxonomy_item_id' => $taxonomyItemId,
                'level' => $practiceLevel,
            ],
        ]);

        $questionMap = Question::query()
            ->select(['id', 'title', 'content', 'result', 'type', 'cat_id'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $questions = collect($questionIds)
            ->map(function ($id) use ($questionMap) {
                return $questionMap[$id] ?? null;
            })
            ->filter()
            ->values();

        return view('homes.luyen_tap_bai_lam', [
            'title' => 'Luyện tập',
            'taxonomyItemId' => $taxonomyItemId,
            'taxonomyName' => $taxonomy->name ?? 'Dạng toán',
            'taxonomyColor' => $taxonomy->color ?: '#2563eb',
            'taxonomyIcon' => $taxonomy->icon_name ?: 'calculate',
            'practiceLevel' => $practiceLevelParam,
            'practiceLevelLabel' => $practiceLevelMeta['label'],
            'practiceLevelColor' => $practiceLevelMeta['color'],
            'practiceSessionKey' => $sessionKey,
            'practiceAttemptId' => $attemptId,
            'questions' => $questions,
            'totalQuestions' => $questions->count(),
        ]);
    }

    public function submitKiemTraDauVao()
    {
        $user = auth()->user();
        if (!$user) {
            return Response::json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để nộp bài.',
            ], 401);
        }

        $examId = (int) Request::input('exam_id', 0);
        $attemptType = (int) Request::input('attempt_type', 0);
        $examQuery = Exams::query()
            ->where('id', $examId)
            ->where('status', 1);

        if ($attemptType === 2) {
            $examQuery->where('test', 0);
        } else {
            $examQuery->where('test', 1);
        }

        $exam = $examQuery->first();

        if (!$exam) {
            return Response::json([
                'success' => false,
                'message' => 'Đề kiểm tra không hợp lệ.',
            ], 422);
        }

        $setting = Settings::first();
        $timeLimit = (int) ($setting->time_test ?? 30);
        $startedAt = (int) Request::input('started_at', 0);
        $taxonomyItemId = (int) Request::input('taxonomy_item_id', 0);
        $isAutoSubmit = (string) Request::input('auto_submit', '0') === '1';
        $answers = Request::input('answers', []);

        $questions = DB::table('exam_question as eq')
            ->join('questions as q', 'eq.question_id', '=', 'q.id')
            ->where('eq.exam_id', $exam->id)
            ->orderBy('eq.order', 'asc')
            ->orderBy('eq.id', 'asc')
            ->get([
                'q.id',
                'q.type',
                'q.result',
            ]);

        $totalQuestions = $questions->count();
        $correctCount = 0;
        $wrongCount = 0;
        $spentSeconds = $startedAt > 0 ? max(0, (int) (now()->timestamp - (int) floor($startedAt / 1000))) : 0;
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'type' => $attemptType,
            'taxonomy_item_id' => $taxonomyItemId,
            'topic_title' => $this->buildAttemptTopicTitle($attemptType, (string) $exam->title),
            'total_questions' => $totalQuestions,
            'correct_count' => 0,
            'wrong_count' => 0,
            'score' => 0,
            'time_limit' => $timeLimit,
            'spent_seconds' => $spentSeconds,
            'status' => $isAutoSubmit ? 2 : 1,
            'started_at' => $startedAt > 0 ? now()->setTimestamp((int) floor($startedAt / 1000)) : now(),
            'submitted_at' => now(),
        ]);

        $resultRows = [];
        foreach ($questions as $question) {
            $submittedAnswer = $answers[$question->id] ?? '';
            $isCorrect = $this->answersMatch($question->type ?? 0, $question->result ?? '', $submittedAnswer);

            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrongCount++;
            }

            $resultRows[] = [
                'attempt_id' => $attempt->id,
                'exam_id' => $exam->id,
                'question_id' => $question->id,
                'user_id' => $user->id,
                'answer' => $this->stringifyAnswer($submittedAnswer),
                'correct_answer' => $this->stringifyAnswer($question->result ?? ''),
                'is_correct' => $isCorrect,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($resultRows)) {
            DB::table('exam_results')->insert($resultRows);
        }

        $attempt->update([
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'score' => $correctCount,
            'status' => $isAutoSubmit ? 2 : 1,
        ]);

        if ($attemptType === 2) {
            session()->forget('thi_thu_exam_id');
        }

        return Response::json([
            'success' => true,
            'message' => 'Nộp bài thành công.',
            'score' => $correctCount,
            'wrong_count' => $wrongCount,
            'percent' => $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0,
            'total_questions' => $totalQuestions,
            'redirect_url' => '/tai-khoan',
            'attempt_id' => $attempt->id,
        ]);
    }

    public function submitLuyenTap()
    {
        $user = auth()->user();
        if (!$user) {
            return Response::json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để nộp bài.',
            ], 401);
        }

        $taxonomyItemId = (int) Request::input('taxonomy_item_id', 0);
        $practiceLevelParam = (int) Request::input('practice_level', 0);
        $practiceLevel = $practiceLevelParam === 0 ? -1 : ($practiceLevelParam - 1);
        $practiceSessionKey = (string) Request::input('practice_session_key', '');
        $practiceAttemptId = (int) Request::input('practice_attempt_id', 0);

        if (empty($practiceSessionKey)) {
            $practiceSessionKey = $this->buildPracticeSessionKey((int) $user->id, $taxonomyItemId, $practiceLevel);
        }

        $practiceSession = session($practiceSessionKey, []);
        $questionIds = $practiceSession['question_ids'] ?? [];
        if (empty($questionIds)) {
            return Response::json([
                'success' => false,
                'message' => 'Không tìm thấy phiên luyện tập hợp lệ.',
            ], 422);
        }

        $questions = Question::query()
            ->select(['id', 'type', 'result'])
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $orderedQuestions = collect($questionIds)
            ->map(function ($id) use ($questions) {
                return $questions[$id] ?? null;
            })
            ->filter()
            ->values();

        $taxonomy = TaxonomyItem::query()
            ->where('taxonomy_id', 1)
            ->find($taxonomyItemId);

        $topicTitle = $this->buildPracticeTopicTitle($taxonomy->name ?? 'Dạng toán', $practiceLevelParam);
        $answers = Request::input('answers', []);
        $correctCount = 0;
        $wrongCount = 0;
        $startedAt = (int) Request::input('started_at', 0);
        $spentSeconds = $startedAt > 0 ? max(0, (int) (now()->timestamp - (int) floor($startedAt / 1000))) : 0;

        $attempt = null;
        if ($practiceAttemptId > 0) {
            $attempt = ExamAttempt::query()
                ->where('id', $practiceAttemptId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (empty($attempt)) {
            $attempt = ExamAttempt::create([
                'exam_id' => null,
                'user_id' => $user->id,
                'type' => 1,
                'taxonomy_item_id' => $taxonomyItemId,
                'topic_title' => $topicTitle,
                'total_questions' => $orderedQuestions->count(),
                'correct_count' => 0,
                'wrong_count' => 0,
                'score' => 0,
                'time_limit' => 0,
                'spent_seconds' => $spentSeconds,
                'status' => 0,
                'started_at' => $startedAt > 0 ? now()->setTimestamp((int) floor($startedAt / 1000)) : now(),
                'submitted_at' => null,
                'question_ids' => json_encode(array_values($questionIds), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }

        $resultRows = [];
        foreach ($orderedQuestions as $question) {
            $submittedAnswer = $answers[$question->id] ?? '';
            $isCorrect = $this->answersMatch($question->type ?? 0, $question->result ?? '', $submittedAnswer);

            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrongCount++;
            }

            $resultRows[] = [
                'attempt_id' => $attempt->id,
                'exam_id' => null,
                'question_id' => $question->id,
                'user_id' => $user->id,
                'answer' => $this->stringifyAnswer($submittedAnswer),
                'correct_answer' => $this->stringifyAnswer($question->result ?? ''),
                'is_correct' => $isCorrect,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($resultRows)) {
            DB::table('exam_results')->insert($resultRows);
        }

        $attempt->update([
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'score' => $correctCount,
            'total_questions' => $orderedQuestions->count(),
            'spent_seconds' => $spentSeconds,
            'status' => 1,
            'submitted_at' => now(),
            'question_ids' => json_encode($orderedQuestions->pluck('id')->values()->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        session()->forget($practiceSessionKey);

        return Response::json([
            'success' => true,
            'message' => 'Nộp bài thành công.',
            'score' => $correctCount,
            'wrong_count' => $wrongCount,
            'percent' => $orderedQuestions->count() > 0 ? (int) round(($correctCount / $orderedQuestions->count()) * 100) : 0,
            'total_questions' => $orderedQuestions->count(),
            'redirect_url' => '/lich-su-lam-bai',
            'attempt_id' => $attempt->id,
        ]);
    }

    public function resetExamSession()
    {
        $type = (string) Request::input('type', '');

        if ($type === 'thi-thu') {
            session()->forget('thi_thu_exam_id');
        }

        return Response::json([
            'success' => true,
        ]);
    }

    public function resumeLuyenTap($attemptId)
    {
        $user = auth()->user();
        $attempt = ExamAttempt::query()
            ->where('id', (int) $attemptId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ((int) ($attempt->type ?? 0) !== 1) {
            return redirect('/lich-su-lam-bai');
        }

        $practiceLevel = $this->getPracticeLevelFromTitle($attempt->topic_title ?? '');
        $savedQuestionIds = [];
        if (!empty($attempt->question_ids)) {
            $decoded = json_decode((string) $attempt->question_ids, true);
            if (is_array($decoded)) {
                $savedQuestionIds = array_values(array_filter($decoded, static fn ($value) => (int) $value > 0));
            }
        }

        if (!empty($savedQuestionIds)) {
            session([
                $this->buildPracticeSessionKey((int) $user->id, (int) ($attempt->taxonomy_item_id ?? 0), $practiceLevel === 0 ? -1 : ($practiceLevel - 1)) => [
                    'attempt_id' => $attempt->id,
                    'question_ids' => $savedQuestionIds,
                    'started_at' => optional($attempt->started_at)->timestamp ? optional($attempt->started_at)->timestamp * 1000 : now()->timestamp * 1000,
                    'taxonomy_item_id' => (int) ($attempt->taxonomy_item_id ?? 0),
                    'level' => $practiceLevel === 0 ? -1 : ($practiceLevel - 1),
                ],
            ]);
        }

        return redirect('/luyen-tap/' . (int) ($attempt->taxonomy_item_id ?? 0) . '/' . $practiceLevel);
    }

    public function lich_su_lam_bai()
    {
        $user = auth()->user();
        $filterType = Request::input('type', '');
        $filterTopic = Request::input('topic', '');

        $attemptsQuery = ExamAttempt::query()
            ->where('user_id', $user->id)
            ->when($filterType !== '' && $filterType !== null, function ($query) use ($filterType) {
                $query->where('type', (int) $filterType);
            })
            ->when($filterTopic !== '' && $filterTopic !== null, function ($query) use ($filterTopic) {
                $query->where('taxonomy_item_id', (int) $filterTopic);
            })
            ->orderByDesc('id');

        $attempts = $attemptsQuery->paginate(10)->withQueryString();
        $attemptStats = (clone $attemptsQuery)->get();
        $attemptIds = $attempts->pluck('id')->all();
        $attemptTopicIds = $attempts->pluck('taxonomy_item_id')->filter()->unique()->values()->all();
        $subjectMap = [];
        $subjectIconMap = [];
        $subjectColorMap = [];
        $attemptTopicRows = !empty($attemptTopicIds)
            ? TaxonomyItem::whereIn('id', $attemptTopicIds)->get(['id', 'name', 'icon_name', 'color'])->keyBy('id')
            : collect();

        foreach ($attempts as $attempt) {
            $topic = $attemptTopicRows[$attempt->taxonomy_item_id] ?? null;
            $subjectMap[$attempt->id] = $topic->name ?? 'Tổng hợp';
            $subjectIconMap[$attempt->id] = $topic->icon_name ?? 'calculate';
            $subjectColorMap[$attempt->id] = $topic->color ?? '#2563eb';
        }

        if (!empty($attemptIds)) {
            $subjectRows = DB::table('exam_results as er')
                ->join('questions as q', 'er.question_id', '=', 'q.id')
                ->whereIn('er.attempt_id', $attemptIds)
                ->select('er.attempt_id', 'q.cat_id', DB::raw('COUNT(*) as total'))
                ->groupBy('er.attempt_id', 'q.cat_id')
                ->get()
                ->groupBy('attempt_id');

            $allCatIds = $subjectRows->flatten(1)->pluck('cat_id')->filter()->unique()->values()->all();
            $catRows = !empty($allCatIds)
                ? TaxonomyItem::whereIn('id', $allCatIds)->get(['id', 'name', 'icon_name', 'color'])->keyBy('id')
                : collect();

            foreach ($subjectRows as $attemptId => $rows) {
                $subjectNames = $rows->map(function ($row) use ($catRows) {
                    return ($catRows[$row->cat_id]->name ?? null) ?: ('Danh mục ' . $row->cat_id);
                })->unique()->values()->all();
                $subjectIcons = $rows->map(function ($row) use ($catRows) {
                    return $catRows[$row->cat_id]->icon_name ?? '';
                })->filter()->unique()->values()->all();
                $subjectColors = $rows->map(function ($row) use ($catRows) {
                    return $catRows[$row->cat_id]->color ?? '';
                })->filter()->unique()->values()->all();

                $subjectMap[$attemptId] = count($subjectNames) > 1 ? 'Tổng hợp' : ($subjectNames[0] ?? 'Tổng hợp');
                $subjectIconMap[$attemptId] = $subjectIcons[0] ?? 'calculate';
                $subjectColorMap[$attemptId] = $subjectColors[0] ?? '#2563eb';
            }
        }
        $topics = [0 => 'Tổng hợp'] + TaxonomyItem::where('taxonomy_id', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $summary = [
            'total_attempts' => $attemptStats->count(),
            'completed_count' => $attemptStats->where('status', 1)->count(),
            'timeout_count' => $attemptStats->where('status', 2)->count(),
            'average_percent' => $attemptStats->count() > 0
                ? (int) round($attemptStats->avg(function ($attempt) {
                    return $attempt->total_questions > 0
                        ? (($attempt->score ?? 0) / $attempt->total_questions) * 100
                        : 0;
                }))
                : 0,
        ];

        return view('homes.lich_su_lam_bai', [
            'title' => 'Lịch sử làm bài',
            'attempts' => $attempts,
            'summary' => $summary,
            'topics' => $topics,
            'filterType' => $filterType,
            'filterTopic' => $filterTopic,
            'subjectMap' => $subjectMap,
            'subjectIconMap' => $subjectIconMap,
            'subjectColorMap' => $subjectColorMap,
        ]);
    }
}
