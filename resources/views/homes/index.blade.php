@extends('layouts.app')
@section('meta')
    <?php use App\Helpers\Utility;$setting = Utility::setting();
    $content = isset($setting->content) ? json_decode($setting->content) : '';
    ?>
    <title>{{$setting->site_title}}</title>
    <meta name="description" content="{{$setting->meta_description}}">
    <meta property="og:title" content="{{$setting->site_title}}">
    <meta name="keywords" content="{{$setting->site_title}}">
    <meta property="og:description" content="{{$setting->meta_description}}">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{Storage::disk('admin')->url($setting->image_og)}}"/>
    <meta name="twitter:card" content="{{$setting->meta_description}}"/>
    <meta name="twitter:site" content="https://tajsc.vn"/>
    <meta name="twitter:title" content="{{$setting->site_title}}"/>
    <meta name="twitter:description" content="{{$setting->meta_description}}"/>
@endsection
@section('content')
    <div class="main-area">
        <header class="topbar px-3 px-md-4 px-xl-5">
            <div class="container-fluid p-0">
                <div class="d-flex align-items-center justify-content-between gap-3 py-3 py-md-4">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <button class="btn sidebar-toggle d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Mở menu">
                            <i class="bi bi-list fs-3"></i>
                        </button>
                        <div class="min-w-0">
                            <h1 class="welcome-title mb-1 text-truncate">Xin chào, Nguyễn Văn An! <span class="wave">👋</span></h1>
                            <p class="welcome-subtitle mb-0">Hôm nay bạn muốn bắt đầu học gì?</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
                        <div class="student-profile d-none d-sm-flex align-items-center gap-2 gap-md-3 px-2 px-md-3 py-2">
                            <div class="avatar">👨🏻‍🎓</div>
                            <div class="d-none d-md-block lh-sm">
                                <div class="fw-bold">Nguyễn Văn An</div>
                                <small class="text-muted">Học sinh lớp 9</small>
                            </div>
                            <i class="bi bi-chevron-down ms-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="content px-3 px-md-4 px-xl-5 pb-4 pb-lg-5">
            <div class="container-fluid p-0">
                <section class="row g-3 g-xl-4 mb-3 mb-xl-4">
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="summary-card score-card h-100">
                            <div class="summary-body">
                                <div>
                                    <div class="card-label">Điểm hiện tại</div>
                                    <div class="score-number">6.2<span>/10</span></div>
                                    <div class="score-level">Mức khá</div>
                                </div>
                                <div class="summary-icon"><i class="bi bi-graph-up-arrow"></i></div>
                            </div>
                            <a href="#" class="card-footer-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                    <div class="col-12 col-md-6 col-xl-4">
                        <article class="summary-card target-card h-100">
                            <div class="summary-body">
                                <div>
                                    <div class="card-label">Mục tiêu điểm số</div>
                                    <div class="score-number">8.0<span>/10</span></div>
                                    <div class="score-level">Cố gắng lên nhé!</div>
                                </div>
                                <div class="summary-icon"><i class="bi bi-bullseye"></i></div>
                            </div>
                            <a href="#" class="card-footer-link">Đặt mục tiêu khác <i class="bi bi-arrow-right"></i></a>
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
                                    <span class="topic-pill danger">Hàm số</span>
                                    <span class="topic-pill warning">Hình học</span>
                                    <span class="topic-pill warning">Thống kê</span>
                                </div>
                            </div>
                            <a href="#" class="card-footer-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
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
                                    <div class="doughnut-center"><strong>6.2</strong><span>Điểm tổng</span></div>
                                </div>
                                <div class="knowledge-list flex-grow-1">
                                    <div class="knowledge-row"><span class="subject-icon algebra"><i class="bi bi-calculator"></i></span><strong>Đại số</strong><span class="ms-auto fw-semibold">7.1 /10</span><span class="status good">Khá</span></div>
                                    <div class="knowledge-row"><span class="subject-icon function"><i class="bi bi-graph-up"></i></span><strong>Hàm số</strong><span class="ms-auto fw-semibold">4.8 /10</span><span class="status improve">Cần cải thiện</span></div>
                                    <div class="knowledge-row"><span class="subject-icon geometry"><i class="bi bi-triangle"></i></span><strong>Hình học</strong><span class="ms-auto fw-semibold">6.5 /10</span><span class="status average">Trung bình</span></div>
                                    <div class="knowledge-row"><span class="subject-icon stats"><i class="bi bi-bar-chart-fill"></i></span><strong>Thống kê &amp; Xác suất</strong><span class="ms-auto fw-semibold">6.0 /10</span><span class="status good">Khá</span></div>
                                </div>
                            </div>
                            <a href="#" class="panel-footer-link">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>

                    <div class="col-12 col-xl-6">
                        <article class="panel-card ai-card h-100">
                            <div class="panel-header px-4 pt-4"><h2 class="section-title mb-0">AI gợi ý cho bạn</h2></div>
                            <div class="ai-body px-4 pt-3 pb-3">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="/assets/images/robot-ai.png" alt="AI robot" class="ai-robot">
                                    <div class="ai-message flex-grow-1">Dựa trên kết quả học tập, AI gợi ý bạn luyện tập nhóm <span>Hàm số</span> để cải thiện điểm số nhé!</div>
                                </div>
                                <div class="fw-bold mb-2">Bài tập đề xuất</div>
                                <div class="exercise-row d-flex flex-wrap align-items-center gap-3">
                                    <span class="exercise-icon"><i class="bi bi-graph-up"></i></span>
                                    <div class="flex-grow-1 min-w-0"><div class="fw-bold">Hàm số bậc nhất <span class="level-badge">Cơ bản</span></div><small class="text-muted">Luyện 10 bài để củng cố kiến thức</small></div>
                                    <button class="btn btn-outline-primary btn-sm px-3">Bắt đầu luyện</button>
                                </div>
                            </div>
                            <a href="#" class="panel-footer-link">Xem tất cả bài tập <i class="bi bi-arrow-right"></i></a>
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
                                <a href="#" class="quick-card quick-blue">
                                    <i class="bi bi-clipboard2-check"></i><strong>Kiểm tra đầu vào</strong>
                                    <small>Đánh giá năng lực</small>
                                </a>
                                <a href="#" class="quick-card quick-green"><i class="bi bi-file-earmark-text"></i><strong>Thi thử</strong><small>Làm đề thi thử</small></a>
                                <a href="#" class="quick-card quick-purple"><i class="bi bi-book"></i><strong>Luyện tập</strong><small>Củng cố kiến thức</small></a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
@stop
@section('script')
<script src="/assets/js/dashboard.js"></script>
@endsection
