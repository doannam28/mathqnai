@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Lịch sử làm bài</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Lịch sử làm bài')

@section('content')
<style>
    .custom-history-pagination .page-item {
        margin: 0 3px;
    }

    .custom-history-pagination .page-link {
        min-width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px !important;
        border: 1px solid #dbe4f0;
        color: #64748b;
        background: #ffffff;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
        padding: 0 12px;
    }

    .custom-history-pagination .page-link:hover {
        color: #2563eb;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .custom-history-pagination .page-item.active .page-link {
        background: #2563eb;
        border-color: #2563eb;
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
    }

    .custom-history-pagination .page-item.disabled .page-link {
        background: #ffffff;
        color: #cbd5e1;
        border-color: #e2e8f0;
        box-shadow: none;
    }
</style>

@php
    $typeLabels = [
        0 => 'Kiểm tra đầu vào',
        1 => 'Luyện tập',
        2 => 'Thi thử',
    ];

    $statusMeta = [
        0 => ['text' => 'Chưa hoàn thành', 'class' => 'warning', 'icon' => 'bi-clock-history'],
        1 => ['text' => 'Hoàn thành', 'class' => 'success'],
        2 => ['text' => 'Hết giờ', 'class' => 'warning'],
    ];

    $currentPage = $attempts->currentPage();
    $lastPage = $attempts->lastPage();
    $visiblePages = [];
    if ($lastPage <= 5) {
        $visiblePages = range(1, $lastPage);
    } elseif ($currentPage <= 3) {
        $visiblePages = [1, 2, 3, '...', $lastPage];
    } elseif ($currentPage >= $lastPage - 2) {
        $visiblePages = [1, '...', $lastPage - 2, $lastPage - 1, $lastPage];
    } else {
        $visiblePages = [1, '...', $currentPage - 1, $currentPage, $currentPage + 1, '...', $lastPage];
    }
@endphp

<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1">Lịch sử làm bài</h1>
            <div class="text-muted">Theo dõi và xem lại kết quả các bài kiểm tra, thi thử và luyện tập của bạn.</div>
        </div>
        <a href="/kiem-tra-dau-vao" class="btn btn-primary px-4">
            <i class="bi bi-clipboard2-check me-2"></i>Làm bài mới
        </a>
    </div>

    <div class="bg-white rounded-4 shadow-sm p-3 p-lg-4 mb-4">
        <form method="GET" action="/lich-su-lam-bai" class="row g-3 align-items-end">
            <div class="col-md-4 col-lg-3">
                <label class="form-label fw-semibold">Loại bài</label>
                <select name="type" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="0" {{ (string)($filterType ?? '') === '0' ? 'selected' : '' }}>Kiểm tra đầu vào</option>
                    <option value="1" {{ (string)($filterType ?? '') === '1' ? 'selected' : '' }}>Ôn tập</option>
                    <option value="2" {{ (string)($filterType ?? '') === '2' ? 'selected' : '' }}>Thi thử</option>
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label fw-semibold">Chủ đề</label>
                <select name="topic" class="form-select">
                    <option value="">Tất cả</option>
                    @foreach($topics as $value => $label)
                        <option value="{{ $value }}" {{ (string)($filterTopic ?? '') === (string)$value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3 ms-auto text-md-end">
                <button type="submit" class="btn btn-outline-primary px-4">
                    <i class="bi bi-funnel me-2"></i>Bộ lọc
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-4 shadow-sm">
        @if($attempts->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr class="text-muted small">
                        <th class="ps-4">Tên bài</th>
                        <th>Loại bài</th>
                        <th>Chủ đề</th>
                        <th>Thời gian làm bài</th>
                        <th>Điểm số</th>
                        <th class="pe-4">Kết quả</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($attempts as $attempt)
                        @php
                            $typeLabel = $typeLabels[(int) ($attempt->type ?? 0)] ?? 'Bài làm';
                            $typeMeta = [
                                0 => ['bg' => '#f59e0b', 'fg' => '#92400e'],
                                1 => ['bg' => '#8b5cf6', 'fg' => '#6d28d9'],
                                2 => ['bg' => '#2563eb', 'fg' => '#1d4ed8'],
                            ][(int) ($attempt->type ?? 0)] ?? ['bg' => '#2563eb', 'fg' => '#1d4ed8'];
                            $meta = $statusMeta[(int) ($attempt->status ?? 0)] ?? $statusMeta[0];
                            $total = (int) ($attempt->total_questions ?? 0);
                            $score = (int) ($attempt->score ?? 0);
                            $wrong = (int) ($attempt->wrong_count ?? max(0, $total - $score));
                            $percent = $total > 0 ? (int) round(($score / $total) * 100) : 0;
                            $spent = (int) ($attempt->spent_seconds ?? 0);
                            $spentMinutes = max(1, (int) ceil($spent / 60));
                            $createdAt = optional($attempt->created_at);
                            $displayTitle = !empty($attempt->topic_title)
                                ? $attempt->topic_title
                                : (((int) ($attempt->type ?? 0) === 0) ? 'Làm bài kiểm tra đầu vào' : 'Không có tiêu đề');
                            $isPractice = (int) ($attempt->type ?? 0) === 1;
                            $iconMap = [
                                0 => 'bi-clipboard2-check',
                                1 => 'bi-book',
                                2 => 'bi-award',
                            ];
                            $rowColor = $isPractice ? ($subjectColorMap[$attempt->id] ?? '#2563eb') : '#2563eb';
                            $rowIcon = $isPractice
                                ? ($subjectIconMap[$attempt->id] ?? 'calculate')
                                : ($iconMap[(int) ($attempt->type ?? 0)] ?? 'bi-clipboard2-check');
                            $subjectColor = $subjectColorMap[$attempt->id] ?? '#2563eb';
                            $scoreClass = 'text-dark';
                            $continueUrl = null;
                            if ((int) ($attempt->status ?? 0) === 0) {
                                if ($isPractice) {
                                    $continueUrl = route('luyen-tap.resume', ['attemptId' => $attempt->id]);
                                } elseif ((int) ($attempt->type ?? 0) === 0) {
                                    $continueUrl = '/kiem-tra-dau-vao';
                                } elseif ((int) ($attempt->type ?? 0) === 2) {
                                    $continueUrl = '/thi-thu';
                                }
                            }
                            if ($isPractice) {
                                $titleLower = mb_strtolower($displayTitle);
                                if (str_contains($titleLower, 'câu hỏi dễ')) {
                                    $scoreClass = 'text-success';
                                } elseif (str_contains($titleLower, 'câu hỏi trung bình')) {
                                    $scoreClass = 'text-warning';
                                } elseif (str_contains($titleLower, 'câu hỏi khó')) {
                                    $scoreClass = 'text-danger';
                                }
                            }
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width: 44px; height: 44px; background: {{ $rowColor }}20; color: {{ $rowColor }};">
                                        @if($isPractice)
                                            <span class="material-symbols-outlined" style="font-size: 22px; line-height: 1;">{{ $rowIcon }}</span>
                                        @else
                                            <i class="bi {{ $rowIcon }} fs-5"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $displayTitle }}</div>
                                        <div class="text-muted small">{{ $total }} câu hỏi</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill text-decoration-none" style="background: {{ $typeMeta['bg'] }}14; color: {{ $typeMeta['fg'] }}; border: 1px solid {{ $typeMeta['bg'] }}33;">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $subjectMap[$attempt->id] ?? 'Tổng hợp' }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $createdAt->format('d/m/Y H:i') }}</div>
                                <div class="text-muted small">{{ $spentMinutes }} phút</div>
                            </td>
                            <td>
                                <div class="fw-bold" style="font-size: 1.05rem;">
                                    <span class="{{ $scoreClass }}" style="font-size: 1.5rem;">{{ $score }}</span> / <span class="text-muted">{{ $total }}</span>
                                </div>
                                <div class="small {{ $scoreClass }}">{{ $percent }}%</div>
                            </td>
                            <td class="pe-4">
                                @if($continueUrl)
                                    <a href="{{ $continueUrl }}" class="badge rounded-pill bg-{{ $meta['class'] }} bg-opacity-10 text-{{ $meta['class'] }} text-decoration-none">
                                        <i class="bi {{ $meta['icon'] ?? 'bi-check2' }} me-1"></i>{{ $meta['text'] }}
                                    </a>
                                @else
                                    <span class="badge rounded-pill bg-{{ $meta['class'] }} bg-opacity-10 text-{{ $meta['class'] }}">
                                        <i class="bi {{ $meta['icon'] ?? 'bi-check2' }} me-1"></i>{{ $meta['text'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 py-3 border-top">
                <div class="text-muted small">
                    Hiển thị {{ $attempts->firstItem() }} - {{ $attempts->lastItem() }} trong {{ $attempts->total() }} kết quả
                </div>
                @if($attempts->lastPage() > 1)
                    <nav aria-label="Phân trang lịch sử">
                        <ul class="pagination pagination-sm mb-0 custom-history-pagination">
                            <li class="page-item {{ $attempts->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $attempts->previousPageUrl() ?: '#' }}" tabindex="-1" aria-disabled="{{ $attempts->onFirstPage() ? 'true' : 'false' }}">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            @foreach($visiblePages as $page)
                                @if($page === '...')
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @else
                                    <li class="page-item {{ (int) $page === $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ $attempts->url($page) }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            <li class="page-item {{ $attempts->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $attempts->nextPageUrl() ?: '#' }}">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                @endif
            </div>
        @else
            <div class="text-center py-5">
                <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 72px; height: 72px;">
                    <i class="bi bi-clock-history fs-2"></i>
                </div>
                <h2 class="h4 fw-bold mb-2">Chưa có lịch sử làm bài</h2>
                <p class="text-muted mb-4">Bạn chưa nộp bài nào. Hãy bắt đầu một bài kiểm tra để hệ thống lưu lại lịch sử.</p>
                <a href="/kiem-tra-dau-vao" class="btn btn-primary px-4">
                    <i class="bi bi-clipboard2-check me-2"></i>Vào làm bài
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
