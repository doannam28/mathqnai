<?php
    $setting = App\Helpers\Utility::setting();
    $currentUser = auth()->user();
    $isHomePage = request()->is('/');
    $navActive = [
        'dashboard' => request()->is('dashboard'),
        'kiem-tra-dau-vao' => request()->is('kiem-tra-dau-vao*'),
        'thi-thu' => request()->is('thi-thu*'),
        'luyen-tap' => request()->is('luyen-tap*'),
        'lich-su-lam-bai' => request()->is('lich-su-lam-bai*'),
        'tai-khoan' => request()->is('tai-khoan*'),
    ];
    $breadcrumbItems = [];
    if (!$isHomePage) {
        $segments = request()->segments();
        $url = '';
        foreach ($segments as $segment) {
            $url .= '/' . $segment;
            $breadcrumbItems[] = [
                'label' => str_replace(['-', '_'], ' ', $segment),
                'url' => $url,
            ];
        }
    }
?>
    <!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php use App\Helpers\Utility;$setting = Utility::setting();
    $content = isset($setting->content) ? json_decode($setting->content) : '';
    ?>
    <title>{{ $title ?? $setting->site_title }}</title>
    <meta name="description" content="{{$setting->meta_description}}">
    <meta property="og:title" content="{{$setting->site_title}}">
    <meta name="keywords" content="{{$setting->site_title}}">
    <meta property="og:description" content="{{$setting->meta_description}}">
    <meta property="og:type" content="article">
    <meta property="og:image" content="{{Storage::disk('admin')->url($setting->image_og)}}"/>
    <meta name="twitter:card" content="{{$setting->meta_description}}"/>
    <meta name="twitter:site" content="{{$setting->website}}"/>
    <meta name="twitter:title" content="{{$setting->site_title}}"/>
    <meta name="twitter:description" content="{{$setting->meta_description}}"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" aria-labelledby="sidebarLabel">
        <div class="sidebar-inner d-flex flex-column h-100">
            <div class="offcanvas-header d-lg-none px-4 pt-4 pb-2">
                <h5 class="offcanvas-title fw-bold" id="sidebarLabel">MATH-QN AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Đóng"></button>
            </div>
            <div class="brand px-4 px-lg-3 px-xl-4 pt-4 pb-3">
                <img src="/assets/images/logo-math-qn-ai-big.png" alt="MATH-QN AI" class="brand-logo img-fluid">
            </div>
            <nav class="main-nav nav flex-column px-3 gap-1" aria-label="Điều hướng chính">
                <a class="nav-link {{ $navActive['dashboard'] ? 'active' : '' }}" href="/dashboard" @if($navActive['dashboard']) aria-current="page" @endif><i class="bi bi-house-door-fill"></i><span>Trang chủ</span></a>
                <a class="nav-link {{ $navActive['kiem-tra-dau-vao'] ? 'active' : '' }}" href="/kiem-tra-dau-vao" @if($navActive['kiem-tra-dau-vao']) aria-current="page" @endif><i class="bi bi-clipboard2-check"></i><span>Kiểm tra đầu vào</span></a>
                <a class="nav-link {{ $navActive['thi-thu'] ? 'active' : '' }}" href="/thi-thu" @if($navActive['thi-thu']) aria-current="page" @endif><i class="bi bi-file-earmark-text"></i><span>Thi thử</span></a>
                <a class="nav-link {{ $navActive['luyen-tap'] ? 'active' : '' }}" href="/luyen-tap" @if($navActive['luyen-tap']) aria-current="page" @endif><i class="bi bi-book"></i><span>Luyện tập</span></a>
                <a class="nav-link {{ $navActive['lich-su-lam-bai'] ? 'active' : '' }}" href="/lich-su-lam-bai" @if($navActive['lich-su-lam-bai']) aria-current="page" @endif><i class="bi bi-clock"></i><span>Lịch sử làm bài</span></a>
                <a class="nav-link {{ $navActive['tai-khoan'] ? 'active' : '' }}" href="/tai-khoan" @if($navActive['tai-khoan']) aria-current="page" @endif><i class="bi bi-person"></i><span>Tài khoản</span></a>
                <a class="nav-link" href="/logout"><i class="bi bi-box-arrow-right"></i><span>Đăng xuất</span></a>
            </nav>
            <div class="sidebar-promo mt-auto mx-3 mb-4 p-3 text-center">
                <img src="/assets/images/robot-trophy.png" alt="Robot học tập" class="promo-image img-fluid">
                <div class="fw-semibold mt-2">Cố gắng hôm nay</div>
                <div class="text-muted small">Để tiến bộ mỗi ngày!</div>
            </div>
        </div>
    </aside>
    <div class="main-area">
        <header class="topbar px-3 px-md-4 px-xl-5">
            <div class="container-fluid p-0">
                <div class="d-flex align-items-center justify-content-between gap-3 py-3 py-md-4">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <button class="btn sidebar-toggle d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Mở menu">
                            <i class="bi bi-list fs-3"></i>
                        </button>
                        <div class="min-w-0">
                            <h1 class="welcome-title mb-1 text-truncate">Xin chào, {{ $currentUser->name ?? 'bạn' }}! <span class="wave">👋</span></h1>
                            @if($isHomePage)
                                <p class="welcome-subtitle mb-0">Hôm nay bạn muốn bắt đầu học gì?</p>
                            @else
                                @if(trim($__env->yieldContent('breadcrumb')))
                                    <div class="mt-1">{!! trim($__env->yieldContent('breadcrumb')) !!}</div>
                                @else
                                    <nav aria-label="breadcrumb" class="mt-1" style="--bs-breadcrumb-divider: '>';">
                                        <ol class="breadcrumb mb-0">
                                            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Trang chủ</a></li>
                                            @foreach($breadcrumbItems as $item)
                                                @if($loop->last)
                                                    <li class="breadcrumb-item active" aria-current="page">{{ ucwords($item['label']) }}</li>
                                                @else
                                                    <li class="breadcrumb-item"><a href="{{ $item['url'] }}" class="text-decoration-none">{{ ucwords($item['label']) }}</a></li>
                                                @endif
                                            @endforeach
                                        </ol>
                                    </nav>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-md-3 flex-shrink-0">
                        <a href="/tai-khoan" title="Tài khoản">
                            <div class="student-profile d-none d-sm-flex align-items-center gap-2 gap-md-3 px-2 px-md-3 py-2">
                                <div class="avatar overflow-hidden bg-primary bg-opacity-10 d-flex align-items-center justify-content-center">
                                    @if($currentUser && $currentUser->avata)
                                        <img src="{{ asset($currentUser->avata) }}" alt="{{ $currentUser->name }}" class="w-100 h-100" style="object-fit: cover;">
                                    @else
                                        👨🏻‍🎓
                                    @endif
                                </div>
                                <div class="d-none d-md-block lh-sm">
                                    <div class="fw-bold">{{ $currentUser->name ?? 'Tài khoản' }}</div>
                                    <small class="text-muted">Học sinh lớp 9</small>
                                </div>
                                <i class="bi bi-chevron-down ms-1"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <main class="content px-3 px-md-4 px-xl-5 pb-4 pb-lg-5">
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
@yield('script')
<script>
(function () {
    function isExamPage() {
        return window.location.pathname.indexOf('/kiem-tra-dau-vao') === 0 || window.location.pathname.indexOf('/thi-thu') === 0;
    }

    function shouldBlockNavigation(link) {
        if (!link || !window.examLeaveGuard || !window.examLeaveGuard.enabled || !isExamPage()) {
            return false;
        }

        if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.noLeaveGuard === '1') {
            return false;
        }

        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#') {
            return false;
        }

        try {
            var url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) {
                return false;
            }

            return !(url.pathname === window.location.pathname && url.search === window.location.search && url.hash === window.location.hash);
        } catch (error) {
            return false;
        }
    }

    function clearExamState() {
        try {
            if (window.sessionStorage && window.examLeaveGuard && window.examLeaveGuard.storageKey) {
                sessionStorage.removeItem(window.examLeaveGuard.storageKey);
            }
        } catch (error) {}
    }

    function resetServerSession() {
        if (!window.examLeaveGuard || !window.examLeaveGuard.resetUrl) {
            return Promise.resolve();
        }

        var formData = new FormData();
        formData.append('type', window.examLeaveGuard.type || '');

        var tokenMeta = document.querySelector('meta[name="csrf-token"]');
        if (tokenMeta && tokenMeta.content) {
            formData.append('_token', tokenMeta.content);
        }

        if (navigator.sendBeacon) {
            navigator.sendBeacon(window.examLeaveGuard.resetUrl, formData);
            return Promise.resolve();
        }

        return fetch(window.examLeaveGuard.resetUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
            keepalive: true,
        }).catch(function () {});
    }

    window.addEventListener('beforeunload', function (event) {
        if (!window.examLeaveGuard || !window.examLeaveGuard.enabled || !isExamPage()) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
        return '';
    });

    window.addEventListener('pagehide', function () {
        if (!window.examLeaveGuard || !window.examLeaveGuard.enabled || !isExamPage()) {
            return;
        }

        clearExamState();
        resetServerSession();
    });

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a[href]') : null;
        if (!shouldBlockNavigation(link)) {
            return;
        }

        event.preventDefault();
        var nextUrl = link.href;
        var confirmExit = function () {
            clearExamState();
            resetServerSession().finally(function () {
                if (window.examLeaveGuard) {
                    window.examLeaveGuard.enabled = false;
                }
                window.location.href = nextUrl;
            });
        };

        if (window.Swal && typeof Swal.fire === 'function') {
            Swal.fire({
                icon: 'warning',
                title: 'Bạn đang làm bài',
                text: 'Bạn có muốn thoát không?',
                showCancelButton: true,
                confirmButtonText: 'Có, thoát',
                cancelButtonText: 'Ở lại',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
            }).then(function (result) {
                if (result.isConfirmed) {
                    confirmExit();
                }
            });
            return;
        }

        if (window.confirm('Bạn đang làm bài, có muốn thoát không?')) {
            confirmExit();
        }
    });
})();
</script>
</body>
</html>
