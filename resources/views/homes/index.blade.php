@extends('layouts.app')
@section('content')
@php
    $currentScoreDisplay = number_format((float) ($currentScore ?? 0), 1, '.', '');
    $targetScoreDisplay = number_format((float) ($targetScore ?? 0), 1, '.', '');
@endphp
    <div class="container-fluid p-0">
                <section class="row g-3 g-xl-4 mb-3 mb-xl-4">
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="summary-card score-card h-100">
                            <div class="summary-body">
                                <div>
                                    <div class="card-label">Điểm hiện tại</div>
                                    <div class="score-number">{{ $currentScoreDisplay }}<span>/10</span></div>
                                    <div class="score-level">{{ $currentScoreLabel ?? 'Chưa có dữ liệu' }}</div>
                                </div>
                                @if(!empty($currentScoreTopic))
                                    <div class="summary-icon round-icon" style="background: {{ $currentScoreTopic['topic_color'] ?? '#2563eb' }}14; color: {{ $currentScoreTopic['topic_color'] ?? '#2563eb' }};">
                                        <span class="material-symbols-outlined" style="font-size: 28px; line-height: 1;">{{ $currentScoreTopic['icon_name'] ?? 'calculate' }}</span>
                                    </div>
                                @else
                                    <div class="summary-icon"><i class="bi bi-graph-up-arrow"></i></div>
                                @endif
                            </div>
                            <a href="/lich-su-lam-bai" class="card-footer-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="summary-card target-card h-100">
                            <div class="summary-body">
                                <div>
                                    <div class="card-label">Mục tiêu điểm số</div>
                                    <div class="score-number">{{ $targetScoreDisplay }}<span>/10</span></div>
                                    <div class="score-level">{{ $targetScoreMessage ?? 'Cố gắng lên nhé!' }}</div>
                                </div>
                                <div class="summary-icon"><i class="bi bi-bullseye"></i></div>
                            </div>
                            <a href="#" id="openTargetScoreModal" class="card-footer-link">Đặt mục tiêu khác <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                    <div class="col-12 col-xl-4">
                        <article class="summary-card attention-card h-100">
                            <div class="summary-body">
                                <div>
                                    <div class="card-label">Chủ đề cần chú ý</div>
                                    <p class="mb-3 mt-4 small">Tập trung cải thiện các chủ đề dưới đây</p>
                                </div>
                                <div class="summary-icon round-icon"><i class="bi bi-exclamation-square-fill"></i></div>
                            </div>
                            <div class="summary-body padding-top-0">
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($attentionTopics ?? [] as $topic)
                                        <span class="topic-pill {{ $topic['class'] }}">{{ $topic['name'] }} <small class="ms-1">({{ number_format($topic['score'], 1) }}/10)</small></span>
                                    @empty
                                        <span class="text-muted small">Không có chủ đề nào dưới 6 điểm.</span>
                                    @endforelse
                                </div>
                            </div>
                            <a href="/lich-su-lam-bai" class="card-footer-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                </section>

                <section class="row g-3 g-xl-4 mb-3 mb-xl-4">
                    <div class="col-12 col-xl-6">
                        <article class="panel-card knowledge-card h-100">
                            <div class="panel-header px-4 pt-4 pb-2"><h2 class="section-title mb-0">Kết quả theo nhóm kiến thức</h2></div>
                            <div class="knowledge-content px-3 px-md-4 pb-3">
                                <div class="chart-wrap doughnut-wrap">
                                    <canvas id="knowledgeChart" aria-label="Biểu đồ kết quả theo nhóm kiến thức"></canvas>
                                    <div class="doughnut-center"><strong>{{ $currentScoreDisplay }}</strong><span>Điểm tổng</span></div>
                                </div>
                                <div class="knowledge-list flex-grow-1">
                                    @forelse($knowledgeBreakdown ?? [] as $topic)
                                        <div class="knowledge-row">
                                            <span class="subject-icon" style="background: {{ $topic['topic_color'] ?? '#2563eb' }}14; color: {{ $topic['topic_color'] ?? '#2563eb' }};">
                                                <span class="material-symbols-outlined" style="font-size: 18px; line-height: 1;">{{ $topic['icon_name'] ?? 'calculate' }}</span>
                                            </span>
                                            <strong>{{ $topic['name'] }}</strong>
                                            <span class="ms-auto fw-semibold">{{ number_format($topic['score'], 1) }} /10</span>
                                            <span class="status {{ $topic['class'] }}">{{ $topic['status'] }}</span>
                                        </div>
                                    @empty
                                        <div class="text-muted small">Chưa có dữ liệu cho biểu đồ này.</div>
                                    @endforelse
                                </div>
                            </div>
                            <a href="/lich-su-lam-bai" class="panel-footer-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>

                    <div class="col-12 col-xl-6">
                        <article class="panel-card ai-card h-100">
                            <div class="panel-header px-4 pt-4"><h2 class="section-title mb-0">AI gợi ý cho bạn</h2></div>
                            <div class="ai-body px-4 pt-3 pb-3">
                                @php
                                    $recommendedTopicName = $recommendedPracticeTopic['name'] ?? 'Hàm số';
                                    $recommendedTopicScore = isset($recommendedPracticeTopic['score']) ? number_format((float) $recommendedPracticeTopic['score'], 1, '.', '') : '0.0';
                                    $recommendedTopicColor = $recommendedPracticeTopic['topic_color'] ?? $recommendedPracticeTopic['color'] ?? '#2563eb';
                                    $recommendedTopicIcon = $recommendedPracticeTopic['icon_name'] ?? 'calculate';
                                    $recommendedLevelLabel = match ((int) ($recommendedPracticeLevel ?? 1)) {
                                        1 => 'Dễ',
                                        2 => 'Trung bình',
                                        3 => 'Khó',
                                        default => 'Dễ',
                                    };
                                @endphp
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="/assets/images/robot-ai.png" alt="AI robot" class="ai-robot">
                                    <div class="ai-message flex-grow-1">Dựa trên kết quả học tập, AI gợi ý bạn luyện tập nhóm <span>{{ $recommendedTopicName }}</span> để cải thiện điểm số nhé!</div>
                                </div>
                                <div class="fw-bold mb-2">Bài tập đề xuất</div>
                                <div class="exercise-row d-flex flex-wrap align-items-center gap-3">
                                    <span class="exercise-icon" style="background: {{ $recommendedTopicColor }}14; color: {{ $recommendedTopicColor }};"><span class="material-symbols-outlined" style="font-size: 22px; line-height: 1;">{{ $recommendedTopicIcon }}</span></span>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-bold">{{ $recommendedTopicName }} <span class="level-badge">{{ $recommendedLevelLabel }}</span></div>
                                        <small class="text-muted">Luyện tập theo mức độ phù hợp với chủ đề đang yếu nhất</small>
                                    </div>
                                    <a href="{{ $recommendedPracticeUrl ?? url('/luyen-tap') }}" class="btn btn-outline-primary btn-sm px-3">Bắt đầu luyện</a>
                                </div>
                            </div>
                            <a href="/luyen-tap" class="panel-footer-link">Xem tất cả bài tập <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                </section>

                <section class="panel-card progress-card">
                    <div class="row g-0">
                        <div class="col-12 col-lg-7 border-end-lg">
                            <div class="px-4 pt-4 pb-2"><h2 class="section-title mb-0">Tiến trình học tập</h2></div>
                            <p class="px-4 pt-4 pb-2">Điểm</p>
                            <div class="progress-chart px-3 px-md-4 pb-3"><canvas id="progressChart" aria-label="Biểu đồ tiến trình học tập"></canvas></div>
                        </div>
                        <div class="col-12 col-lg-5">
                            <div class="px-4 pt-4 pb-2"><h2 class="section-title mb-3">Truy cập nhanh</h2></div>
                            <div class="quick-grid px-3 px-md-4 pb-4">
                                <a href="/kiem-tra-dau-vao" class="quick-card quick-blue">
                                    <i class="bi bi-clipboard2-check"></i><strong>Kiểm tra đầu vào</strong>
                                    <small>Đánh giá năng lực</small>
                                </a>
                                <a href="/thi-thu" class="quick-card quick-green"><i class="bi bi-file-earmark-text"></i><strong>Thi thử</strong><small>Làm đề thi thử</small></a>
                                <a href="/luyen-tap" class="quick-card quick-purple"><i class="bi bi-book"></i><strong>Luyện tập</strong><small>Củng cố kiến thức</small></a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

    <div class="modal fade" id="targetScoreModal" tabindex="-1" aria-labelledby="targetScoreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="{{ route('tai-khoan.target-score') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="targetScoreModalLabel">Đặt mục tiêu điểm số</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Nhập mục tiêu bạn muốn đạt được, từ 0 đến 10.</p>
                        <label class="form-label fw-semibold" for="target_score_input">Mục tiêu điểm số</label>
                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            max="10"
                            name="target_score"
                            id="target_score_input"
                            class="form-control form-control-lg"
                            value="{{ $targetScoreDisplay }}"
                            required
                        >
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu mục tiêu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('script')
<script>
(function () {
    const opener = document.getElementById('openTargetScoreModal');
    const modalEl = document.getElementById('targetScoreModal');
    if (!opener || !modalEl || typeof bootstrap === 'undefined') return;

    opener.addEventListener('click', function (event) {
        event.preventDefault();
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });
})();
</script>
<script>
window.dashboardKnowledgeChartData = {
    labels: @json($knowledgeChartLabels ?? []),
    values: @json($knowledgeChartValues ?? []),
    colors: @json($knowledgeChartColors ?? []),
};
window.dashboardProgressChartData = {
    labels: @json($progressChartLabels ?? []),
    values: @json($progressChartValues ?? []),
};
</script>
<script src="/assets/js/dashboard.js"></script>
@endsection
