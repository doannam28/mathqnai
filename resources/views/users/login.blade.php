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
    <link rel="stylesheet" href="/assets/css/register.css">
    <title>Đăng nhập tài khoản</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<main class="register-page container-fluid">
    <header aria-label="MATH-QN AI" class="brand d-flex align-items-center justify-content-center">
        <img alt="Biểu tượng MATH-QN AI" class="brand-mascot" src="assets/images/mascot.svg"/>
        <div class="brand-copy">
            <div class="brand-name">MATH-QN <span>AI</span></div>
            <div class="brand-tagline">Hệ thống học Toán cá nhân hóa</div>
        </div>
    </header>
    <section aria-labelledby="register-title" class="register-card mx-auto">
        <h1 id="register-title">Đăng nhập</h1>
        <p class="subtitle">Chào mừng bạn trở lại!</p>
        <form class="needs-validation" id="registerForm" novalidate="">
            @csrf
            <div class="field">
                <label class="form-label" for="email">Email</label>
                <div class="input-icon"><i aria-hidden="true" class="bi bi-envelope"></i><input autocomplete="email" class="form-control" id="email" name="email" placeholder="Nhập email" required="" type="email"/></div>
                <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div></div>
            <div class="field">
                <label class="form-label" for="password">Mật khẩu</label>
                <div class="input-icon has-action"><i aria-hidden="true" class="bi bi-lock"></i><input autocomplete="new-password" class="form-control" id="password" minlength="8" name="password" placeholder="Nhập mật khẩu" required="" type="password"/><button aria-label="Hiện mật khẩu" class="input-action toggle-password" data-target="password" type="button"><i class="bi bi-eye"></i></button></div>
                <div class="invalid-feedback">Vui lòng nhập mật khẩu.</div></div>
            <div aria-live="polite" class="form-feedback" id="formFeedback" role="status"></div>
            <button class="btn btn-register w-100" type="submit">Đăng nhập</button>
        </form>
        <div class="separator"><span>hoặc</span></div>
        <a href="{{ route('google.login') }}" class="btn btn-google w-100" id="googleRegister"><img alt="" height="22" src="assets/images/google-g.svg" width="22"/>Đăng nhập với Google</a>
        <p class="login-text">Chưa có tài khoản? <a href="/dang-ky">Đăng ký</a></p>
    </section>
</main>
<script>
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const feedback = document.getElementById('formFeedback');

    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            const showing = input.type === 'password';
            input.type = showing ? 'text' : 'password';
            button.querySelector('i').className = showing ? 'bi bi-eye-slash' : 'bi bi-eye';
            button.setAttribute('aria-label', showing ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });

    const nativeDate = document.getElementById('nativeDate');
    form.addEventListener('submit', event => {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        feedback.textContent = '';
        feedback.className = 'form-feedback';
        if (!form.checkValidity()) return;
        let formData = new FormData(form);
        fetch("{{ url('/submit-login') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    window.location.href = data.redirect;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Đăng nhập thất bại!',
                        text: data.message || 'Email hoặc mật khẩu không chính xác.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi đăng nhập.');
            });
    });

    document.getElementById('googleRegister').addEventListener('click', () => {
        feedback.textContent = 'Cần kết nối Google OAuth để sử dụng đăng ký với Google.';
        feedback.className = 'form-feedback';
    });
</script>
</body>
</html>
