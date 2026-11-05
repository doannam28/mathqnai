@extends('layouts.app')
@section('content')
@php
    $birthDateValue = old('birth_date', $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '');
    $avatarPath = $user->avata ? asset($user->avata) : null;
    $avatarLetter = mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1));
@endphp
<div class="container py-4 py-lg-5">
    <div class="mb-4">
        <h1 class="fw-bold mb-1">Tài khoản</h1>
        <div class="text-muted">Quản lý thông tin tài khoản của bạn.</div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-person-circle text-primary fs-4"></i>
                <h2 class="h5 fw-bold mb-0">Thông tin cá nhân</h2>
            </div>

            <div class="row g-4 align-items-start">
                <div class="col-12 col-lg-3 text-center text-lg-start">
                    <div class="position-relative d-inline-block">
                        <div class="rounded-circle overflow-hidden bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                             style="width: 160px; height: 160px;">
                            @if ($avatarPath)
                                <img src="{{ $avatarPath }}" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">
                            @else
                                <span class="text-primary fw-bold" style="font-size: 72px;">{{ $avatarLetter }}</span>
                            @endif
                        </div>
                        <label for="avata" class="position-absolute bottom-0 end-0 btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                               style="width: 42px; height: 42px; cursor: pointer;">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" id="avata" name="avata" accept="image/*" class="d-none" form="accountForm">
                    </div>
                    <div class="mt-3 text-muted small">Bấm biểu tượng máy ảnh để đổi ảnh đại diện</div>
                </div>

                <div class="col-12 col-lg-9">
                    <form id="accountForm" method="POST" action="/tai-khoan" class="row g-3" enctype="multipart/form-data">
                        @csrf
                        <div class="col-12">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input
                                type="text"
                                class="form-control form-control-lg"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                placeholder="Nhập họ và tên"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input
                                    type="email"
                                    class="form-control bg-light text-secondary"
                                    id="email"
                                    value="{{ $user->email }}"
                                    readonly
                                    aria-readonly="true"
                                    style="cursor: not-allowed;"
                                >
                                <span class="input-group-text bg-light text-secondary">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                            </div>
                            <div class="form-text text-secondary">Email không thể thay đổi.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="sex" class="form-label">Giới tính</label>
                            <select class="form-select form-select-lg" id="sex" name="sex" required>
                                <option value="">Chọn giới tính</option>
                                <option value="0" {{ old('sex', (string) $user->sex) === '0' ? 'selected' : '' }}>Nam</option>
                                <option value="1" {{ old('sex', (string) $user->sex) === '1' ? 'selected' : '' }}>Nữ</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="birth_date" class="form-label">Ngày sinh</label>
                            <input
                                type="date"
                                class="form-control form-control-lg"
                                id="birth_date"
                                name="birth_date"
                                value="{{ $birthDateValue }}"
                            >
                        </div>

                        <div class="col-12">
                            <div class="p-3 p-lg-4 border rounded-4 bg-light">
                                <div class="fw-bold mb-1">Đổi mật khẩu</div>
                                <div class="text-muted small mb-3">Bỏ trống nếu bạn không muốn đổi mật khẩu.</div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="password" class="form-label">Mật khẩu mới</label>
                                        <input
                                            type="password"
                                            class="form-control form-control-lg"
                                            id="password"
                                            name="password"
                                            minlength="8"
                                            placeholder="Nhập mật khẩu mới"
                                        >
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
                                        <input
                                            type="password"
                                            class="form-control form-control-lg"
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            minlength="8"
                                            placeholder="Nhập lại mật khẩu mới"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    const avatarInput = document.getElementById('avata');
    const avatarWrap = avatarInput ? avatarInput.closest('.col-lg-3') : null;

    if (avatarInput && avatarWrap) {
        avatarInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const circle = avatarWrap.querySelector('.rounded-circle');
                if (!circle) return;

                circle.innerHTML = '<img src="' + e.target.result + '" alt="Avatar" class="w-100 h-100" style="object-fit: cover;">';
            };
            reader.readAsDataURL(file);
        });
    }
</script>
@endsection
