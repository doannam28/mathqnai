<?php $setting = App\Helpers\Utility::setting();?>
    <!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                <a class="nav-link active" href="#"><i class="bi bi-house-door-fill"></i><span>Trang chủ</span></a>
                <a class="nav-link" href="#"><i class="bi bi-clipboard2-check"></i><span>Kiểm tra đầu vào</span></a>
                <a class="nav-link" href="#"><i class="bi bi-file-earmark-text"></i><span>Thi thử</span></a>
                <a class="nav-link" href="#"><i class="bi bi-book"></i><span>Luyện tập</span></a>
                <a class="nav-link" href="#"><i class="bi bi-clock"></i><span>Lịch sử làm bài</span></a>
                <a class="nav-link" href="#"><i class="bi bi-person"></i><span>Tài khoản</span></a>
                <a class="nav-link" href="#"><i class="bi bi-box-arrow-right"></i><span>Đăng xuất</span></a>
            </nav>
            <div class="sidebar-promo mt-auto mx-3 mb-4 p-3 text-center">
                <img src="/assets/images/robot-trophy.png" alt="Robot học tập" class="promo-image img-fluid">
                <div class="fw-semibold mt-2">Cố gắng hôm nay</div>
                <div class="text-muted small">Để tiến bộ mỗi ngày!</div>
            </div>
        </div>
    </aside>
    @yield('content')
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYjW4cR5rZ9LrKj4h2Yx9w9fZf" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
@yield('script')
</body>
</html>
