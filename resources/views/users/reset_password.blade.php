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
    <title>Đặt lại mật khẩu</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/x-icon" href="{{Storage::disk('admin')->url($setting->favicon)}}">
</head>
<body>
<main class="register-page container-fluid">
    <header aria-label="MATH-QN AI" class="brand d-flex align-items-center justify-content-center">
        <img alt="{{$setting->name}}" class="img-logo" src="{{Storage::disk('admin')->url($setting->logo)}}"/>
    </header>
    <section aria-labelledby="reset-title" class="register-card mx-auto">
        <h1 id="reset-title">Đặt lại mật khẩu</h1>
        <p class="subtitle">Nhập mật khẩu mới để tiếp tục đăng nhập.</p>

        <form method="POST" action="{{ route('password.update') }}" class="needs-validation" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field">
                <label class="form-label" for="email">Email</label>
                <div class="input-icon">
                    <i aria-hidden="true" class="bi bi-envelope"></i>
                    <input
                        autocomplete="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="email"
                        name="email"
                        required
                        type="email"
                        value="{{ old('email', $email) }}"
                    />
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div>
                @enderror
            </div>

            <div class="field">
                <label class="form-label" for="password">Mật khẩu mới</label>
                <div class="input-icon has-action">
                    <i aria-hidden="true" class="bi bi-lock"></i>
                    <input
                        autocomplete="new-password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        minlength="8"
                        name="password"
                        placeholder="Nhập mật khẩu mới"
                        required
                        type="password"
                    />
                    <button aria-label="Hiện mật khẩu" class="input-action toggle-password" data-target="password" type="button"><i class="bi bi-eye"></i></button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập mật khẩu mới.</div>
                @enderror
            </div>

            <div class="field">
                <label class="form-label" for="password_confirmation">Nhập lại mật khẩu</label>
                <div class="input-icon has-action">
                    <i aria-hidden="true" class="bi bi-shield-lock"></i>
                    <input
                        autocomplete="new-password"
                        class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation"
                        minlength="8"
                        name="password_confirmation"
                        placeholder="Nhập lại mật khẩu"
                        required
                        type="password"
                    />
                    <button aria-label="Hiện mật khẩu" class="input-action toggle-password" data-target="password_confirmation" type="button"><i class="bi bi-eye"></i></button>
                </div>
                @error('password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @else
                    <div class="invalid-feedback">Vui lòng nhập lại mật khẩu.</div>
                @enderror
            </div>

            <button class="btn btn-register w-100" type="submit">Đổi mật khẩu</button>
        </form>

        <p class="login-text mb-0"><a href="/dang-nhap">Quay lại đăng nhập</a></p>
    </section>
</main>
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            button.querySelector('i').className = showing ? 'bi bi-eye-slash' : 'bi bi-eye';
            button.setAttribute('aria-label', showing ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });

    @if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Không thể đổi mật khẩu',
        text: @json($errors->first()),
    });
    @endif
</script>
</body>
</html>
