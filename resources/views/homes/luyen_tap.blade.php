@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Luyện tập</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Luyện tập')

@section('content')
<div class="container py-4 py-lg-5">
    @php
        $levelOptions = [
            0 => ['label' => 'Random (Ngẫu nhiên)', 'hint' => 'Hệ thống tự chọn mức độ phù hợp khi bắt đầu.', 'badge' => 'secondary'],
            1 => ['label' => 'Mức độ 1 - Dễ', 'hint' => 'Chỉ lấy câu hỏi dễ.', 'badge' => 'primary'],
            2 => ['label' => 'Mức độ 2 - Trung bình', 'hint' => 'Chỉ lấy câu hỏi trung bình.', 'badge' => 'warning'],
            3 => ['label' => 'Mức độ 3 - Khó', 'hint' => 'Chỉ lấy câu hỏi khó.', 'badge' => 'danger'],
        ];
        $currentLevel = (int) ($selectedLevel ?? 0);
        if (!array_key_exists($currentLevel, $levelOptions)) {
            $currentLevel = 0;
        }
        $currentLevelLabel = $currentLevel === 0
            ? $levelOptions[0]['label']
            : (($selectedLevelMeta['label'] ?? $levelOptions[1]['label']));
        $currentLevelHint = $currentLevel === 0
            ? $levelOptions[0]['hint']
            : ($levelOptions[$currentLevel]['hint'] ?? '');
        $currentLevelBadge = $currentLevel === 0
            ? $levelOptions[0]['badge']
            : (($selectedLevelMeta['badge'] ?? 'primary'));
    @endphp

    <div class="mb-4">
        <h1 class="fw-bold mb-1">Luyện tập</h1>
        <div class="text-muted">Chọn mức độ trước, sau đó bắt đầu luyện tập theo từng dạng toán.</div>
    </div>

    <div class="bg-white border rounded-4 shadow-sm p-3 p-lg-4 mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-lg-2">
                <div class="fw-bold">Chọn mức độ</div>
            </div>
            <div class="col-lg-4 col-xl-4">
                @php
                    $dropdownOptions = [
                        0 => ['label' => $levelOptions[0]['label'], 'text' => '↻', 'bg' => '#e2e8f0', 'fg' => '#475569'],
                        1 => ['label' => $levelOptions[1]['label'], 'text' => '1', 'bg' => '#22c55e', 'fg' => '#ffffff'],
                        2 => ['label' => $levelOptions[2]['label'], 'text' => '2', 'bg' => '#f59e0b', 'fg' => '#ffffff'],
                        3 => ['label' => $levelOptions[3]['label'], 'text' => '3', 'bg' => '#ef4444', 'fg' => '#ffffff'],
                    ];
                    $currentDropdown = $dropdownOptions[$currentLevel] ?? $dropdownOptions[0];
                @endphp
                <div class="dropdown w-100" id="practiceLevelDropdown">
                    <button
                        class="btn bg-white border w-100 d-flex align-items-center justify-content-between px-3"
                        type="button"
                        id="practiceLevelToggle"
                        aria-expanded="false"
                        style="height: 42px; border-radius: 12px; border-color: #dbe4f0; box-shadow: 0 8px 24px rgba(15, 23, 42, .05); font-size: 14px; font-weight: 500;"
                    >
                        <span class="d-flex align-items-center gap-2">
                            <span id="practiceLevelIcon"
                                  class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                  style="width: 22px; height: 22px; background: {{ $currentDropdown['bg'] }}; color: {{ $currentDropdown['fg'] }}; font-size: 10px; font-weight: 700; line-height: 1;">
                                {{ $currentDropdown['text'] }}
                            </span>
                            <span id="practiceLevelLabel" class="text-dark" style="font-size: 14px;">{{ $currentDropdown['label'] }}</span>
                        </span>
                        <i class="bi bi-chevron-down text-secondary"></i>
                    </button>
                    <ul class="dropdown-menu w-100 shadow-sm border-0 mt-2 p-1" style="border-radius: 14px;">
                        @foreach($dropdownOptions as $value => $option)
                            <li>
                                <a href="#"
                                   class="dropdown-item practice-level-item d-flex align-items-center gap-2 py-2 px-3 rounded-3"
                                   data-level="{{ $value }}"
                                   data-label="{{ $option['label'] }}"
                                   data-text="{{ $option['text'] }}"
                                   data-bg="{{ $option['bg'] }}"
                                   data-fg="{{ $option['fg'] }}"
                                   style="font-size: 14px; {{ $currentLevel === $value ? 'background: rgba(37, 99, 235, .08); color: #2563eb;' : '' }}">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                          style="width: 22px; height: 22px; background: {{ $option['bg'] }}; color: {{ $option['fg'] }}; font-size: 10px; font-weight: 700; line-height: 1;">
                                        {{ $option['text'] }}
                                    </span>
                                    <span style="{{ $currentLevel === $value ? 'color: #2563eb; font-weight: 600;' : 'color: #111827;' }}">{{ $option['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 col-xl-6">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge rounded-pill text-bg-{{ $currentLevelBadge }}">
                        {{ $currentLevelLabel }}
                    </span>
                    <span class="text-muted">{{ $currentLevelHint }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-xl-4">
        @forelse($topics as $topic)
            @php
                $topicColor = $topic->color ?: '#2563eb';
                $topicIcon = $topic->icon_name ?: 'calculate';
                $questionCount = (int) ($topicQuestionCounts[$topic->id] ?? 0);
                $startUrl = url('/luyen-tap/' . $topic->id . '/' . $currentLevel);
            @endphp
            <div class="col-12 col-md-6 col-xl-4">
                <div class="bg-white border rounded-4 shadow-sm p-4 h-100 d-flex flex-column">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 72px; height: 72px; background: {{ $topicColor }}18; color: {{ $topicColor }};">
                            <span class="material-symbols-outlined" style="font-size: 34px; line-height: 1;">{{ $topicIcon }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5 lh-sm">{{ $topic->name }}</div>
                            <div class="text-muted small mt-1 lh-sm">
                                {{ $topic->description ?: 'Chọn mức độ và bấm bắt đầu để luyện tập theo chủ đề này.' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <div class="row g-3 align-items-center mb-3">
                            <div class="col-5">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="text-primary" style="font-size: 18px; line-height: 1.1;">
                                        <i class="bi bi-bookmark"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-5 lh-1">{{ number_format($questionCount) }}</div>
                                        <div class="text-muted small mt-1">bài tập</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-7">
                                <div class="text-muted small mb-1">Mức độ</div>
                                <div class="d-flex align-items-center gap-1">
                                    @for($i = 1; $i <= 3; $i++)
                                        @php
                                            $filledDots = $currentLevel > 0 ? $currentLevel : 0;
                                            $dotOpacity = $i <= $filledDots ? '1' : '.18';
                                        @endphp
                                        <span class="d-inline-block rounded-pill"
                                              style="width: 10px; height: 10px; background: {{ $topicColor }}; opacity: {{ $dotOpacity }};"></span>
                                    @endfor
                                </div>
                                <div class="text-muted small mt-1">{{ $currentLevelLabel }}</div>
                            </div>
                        </div>

                        <a href="{{ $startUrl }}" class="btn btn-outline-primary w-100 py-2 fw-semibold">
                            Bắt đầu luyện tập <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning mb-0">Chưa có dạng toán nào để luyện tập.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@section('script')
<script>
(function () {
    const dropdown = document.getElementById('practiceLevelDropdown');
    if (!dropdown) return;

    const toggleBtn = document.getElementById('practiceLevelToggle');
    const label = document.getElementById('practiceLevelLabel');
    const iconWrap = document.getElementById('practiceLevelIcon');
    const menu = dropdown.querySelector('.dropdown-menu');
    const levelItems = dropdown.querySelectorAll('.practice-level-item');

    function closeMenu() {
        if (!menu) return;
        menu.classList.remove('show');
        dropdown.classList.remove('show');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    }

    function toggleMenu() {
        if (!menu) return;
        const isOpen = menu.classList.contains('show');
        if (isOpen) {
            closeMenu();
        } else {
            menu.classList.add('show');
            dropdown.classList.add('show');
            if (toggleBtn) {
                toggleBtn.setAttribute('aria-expanded', 'true');
            }
        }
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            toggleMenu();
        });
    }

    levelItems.forEach((item) => {
        item.addEventListener('click', function (event) {
            event.preventDefault();

            const level = this.dataset.level || '0';
            const text = this.dataset.text || '↻';
            const bg = this.dataset.bg || '#e2e8f0';
            const fg = this.dataset.fg || '#475569';
            const nextLabel = this.dataset.label || '';

            if (iconWrap) {
                iconWrap.textContent = text;
                iconWrap.style.backgroundColor = bg;
                iconWrap.style.color = fg;
            }

            if (label) {
                label.textContent = nextLabel;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('level', level);
            window.location.href = url.toString();
        });
    });

    document.addEventListener('click', function (event) {
        if (!dropdown.contains(event.target)) {
            closeMenu();
        }
    });
})();
</script>
@endsection
