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
    <title>Đăng ký tài khoản</title>
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
        <h1 id="register-title">Đăng ký</h1>
        <p class="subtitle">Tạo tài khoản để bắt đầu học tập!</p>
        <form class="needs-validation" id="registerForm" novalidate="">
            @csrf
            <div class="field">
                <label class="form-label" for="fullName">Họ và tên</label>
                <div class="input-icon"><i aria-hidden="true" class="bi bi-person"></i><input autocomplete="name" class="form-control" id="fullName" name="full_name" placeholder="Nhập họ và tên" required=""/></div>
                <div class="invalid-feedback">Vui lòng nhập họ và tên.</div></div>
            <div class="field">
                <label class="form-label" for="email">Email</label>
                <div class="input-icon"><i aria-hidden="true" class="bi bi-envelope"></i><input autocomplete="email" class="form-control" id="email" name="email" placeholder="Nhập email" required="" type="email"/></div>
                <div class="invalid-feedback">Vui lòng nhập email hợp lệ.</div></div>
            <div class="field">
                <label class="form-label" for="password">Mật khẩu</label>
                <div class="input-icon has-action"><i aria-hidden="true" class="bi bi-lock"></i><input autocomplete="new-password" class="form-control" id="password" minlength="8" name="password" placeholder="Nhập mật khẩu" required="" type="password"/><button aria-label="Hiện mật khẩu" class="input-action toggle-password" data-target="password" type="button"><i class="bi bi-eye"></i></button></div>
                <div class="invalid-feedback">Vui lòng nhập mật khẩu tối thiểu 8 ký tự.</div></div>
            <div class="field">
                <label class="form-label" for="confirmPassword">Nhập lại mật khẩu</label>
                <div class="input-icon has-action"><i aria-hidden="true" class="bi bi-lock"></i><input autocomplete="new-password" class="form-control" id="confirmPassword" name="password_confirmation" placeholder="Nhập lại mật khẩu" required="" type="password"/><button aria-label="Hiện mật khẩu" class="input-action toggle-password" data-target="confirmPassword" type="button"><i class="bi bi-eye"></i></button></div>
                <div class="invalid-feedback">Vui lòng nhập lại mật khẩu trùng khớp.</div></div>
            <div class="field last-field">
                <label class="form-label" for="birthDate">Ngày sinh</label>
                <div class="input-icon has-action"><i aria-hidden="true" class="bi bi-calendar3"></i><input class="form-control" id="birthDate" inputmode="numeric" maxlength="10" name="birth_date" placeholder="DD/MM/YYYY" required="" type="text"/><button aria-label="Chọn ngày sinh" class="input-action" id="calendarButton" type="button"><i class="bi bi-calendar3"></i></button><input aria-label="Lịch chọn ngày sinh" class="native-date" id="nativeDate" tabindex="-1" type="date"/></div>
                <div class="invalid-feedback">Vui lòng nhập ngày sinh hợp lệ (DD/MM/YYYY).</div></div>


            <div aria-live="polite" class="form-feedback" id="formFeedback" role="status"></div>
            <button class="btn btn-register w-100" type="submit">Đăng ký</button>
        </form>
        <div class="separator"><span>hoặc</span></div>
        <button class="btn btn-google w-100" id="googleRegister" type="button"><img alt="" height="22" src="assets/images/google-g.svg" width="22"/>Đăng ký với Google</button>
        <p class="login-text">Đã có tài khoản? <a href="/dang-nhap">Đăng nhập</a></p>
    </section>
</main>
<script>
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const birthDate = document.getElementById('birthDate');
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
    document.getElementById('calendarButton').addEventListener('click', () => {
        if (nativeDate.showPicker) nativeDate.showPicker(); else nativeDate.click();
    });
    nativeDate.addEventListener('change', () => {
        if (!nativeDate.value) return;
        const [y, m, d] = nativeDate.value.split('-');
        birthDate.value = `${d}/${m}/${y}`;
        birthDate.dispatchEvent(new Event('input', { bubbles: true }));
    });

    function validateBirthDate() {
        const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(birthDate.value.trim());
        let valid = false;
        if (match) {
            const d = Number(match[1]), m = Number(match[2]), y = Number(match[3]);
            const date = new Date(y, m - 1, d);
            valid = y >= 1900 && date.getFullYear() === y && date.getMonth() === m - 1 &&
                date.getDate() === d && date <= new Date();
        }
        birthDate.setCustomValidity(valid ? '' : 'Ngày sinh không hợp lệ');
    }

    function validateConfirmation() {
        confirmPassword.setCustomValidity(
            confirmPassword.value && confirmPassword.value !== password.value
                ? 'Mật khẩu không trùng khớp' : ''
        );
    }

    password.addEventListener('input', validateConfirmation);
    confirmPassword.addEventListener('input', validateConfirmation);
    birthDate.addEventListener('input', validateBirthDate);

    form.addEventListener('submit', event => {
        event.preventDefault();
        event.stopPropagation();
        validateBirthDate();
        validateConfirmation();
        form.classList.add('was-validated');
        feedback.textContent = '';
        feedback.className = 'form-feedback';
        if (!form.checkValidity()) return;
        let formData = new FormData(form);
        fetch("{{ url('/submit-register') }}", {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if(data.success){
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                    form.reset();
                    form.classList.remove('was-validated');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: 'Đăng ký thất bại: ' + (data.message || 'Lỗi server'),
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Đã xảy ra lỗi khi đăng ký.');
            });
    });

    document.getElementById('googleRegister').addEventListener('click', () => {
        feedback.textContent = 'Cần kết nối Google OAuth để sử dụng đăng ký với Google.';
        feedback.className = 'form-feedback';
    });
</script>
</body>
</html>
