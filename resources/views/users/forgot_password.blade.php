<?php $setting = App\Helpers\Utility::setting();?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/register.css">
    <title>Quên mật khẩu</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/x-icon" href="{{Storage::disk('admin')->url($setting->favicon)}}">
</head>
<body>
<main class="register-page container-fluid">
    <header aria-label="MATH-QN AI" class="brand d-flex align-items-center justify-content-center">
        <img alt="{{$setting->name}}" class="img-logo" src="{{Storage::disk('admin')->url($setting->logo)}}"/>
    </header>
    <section aria-labelledby="forgot-title" class="register-card mx-auto">
        <h1 id="forgot-title">Quên mật khẩu</h1>
        <p class="subtitle">Nhập email để nhận liên kết đặt lại mật khẩu.</p>

        <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
            @csrf
            <div class="field">
                <label class="form-label" for="email">Email</label>
                <div class="input-icon">
                    <i aria-hidden="true" class="bi bi-envelope"></i>
                    <input
                        autocomplete="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        placeholder="Nhập email đã đăng ký"
                        required
                        type="email"
                        value="{{ old('email') }}"
                    />
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                @enderror
            </div>

            <button class="btn btn-register w-100" type="submit">Gửi link đặt lại mật khẩu</button>
        </form>

        <p class="login-text mb-0">Đã nhớ mật khẩu? <a href="/dang-nhap">Quay lại đăng nhập</a></p>
    </section>
</main>
<script>
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Đã gửi email',
        text: @json(session('success')),
    });
    @endif
</script>
</body>
</html>
