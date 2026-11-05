@extends('layouts.app')

@section('breadcrumb')
<nav aria-label="breadcrumb" style="--bs-breadcrumb-divider: '>';">
    <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="/kiem-tra-dau-vao" class="text-decoration-none">Kiểm tra đầu vào</a></li>
        <li class="breadcrumb-item active" aria-current="page">Làm bài</li>
    </ol>
</nav>
@endsection

@section('page_title', 'Làm bài')

@section('content')
@php
    $totalQuestions = $totalQuestions ?? $questions->count();
    $timeLabel = sprintf('%02d:%02d', $timeLimit, 0);
    $durationLabel = $timeLimit . 'p';
@endphp

<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="fw-bold mb-1">Kiểm tra đầu vào</h1>
            <div class="text-muted">Làm bài trắc nghiệm và nộp khi hoàn thành.</div>
        </div>
        <div class="text-end">
            <div class="text-muted small mb-1">Thời gian làm bài</div>
            <div class="fw-bold fs-4 text-primary">
                <i class="bi bi-stopwatch me-1"></i><span id="timerText">{{ $durationLabel }}</span>
            </div>
        </div>
    </div>

    <form id="examForm" action="{{ route('kiem-tra-dau-vao.submit') }}" method="POST" class="bg-white rounded-4 shadow-sm p-3 p-lg-4" data-exam-id="{{ $examId ?? 0 }}" data-time-limit="{{ $timeLimit }}" data-total="{{ $totalQuestions }}">
        @csrf
        <input type="hidden" name="exam_id" value="{{ $examId ?? 0 }}">
        <input type="hidden" name="attempt_type" value="{{ $attemptType ?? 0 }}">
        <input type="hidden" name="taxonomy_item_id" value="{{ $taxonomyItemId ?? 0 }}">
        <input type="hidden" name="started_at" id="startedAt" value="">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom pb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                     style="width: 72px; height: 72px;">
                    <i class="bi bi-clock-history fs-2"></i>
                </div>
                <div>
                    <div class="text-muted small">Thời gian còn lại</div>
                    <div class="fs-4 fw-bold text-primary" id="timerTextBig">{{ $timeLabel }}</div>
                </div>
            </div>

            <div class="text-center">
                <div class="text-muted small">Câu</div>
                <div class="fw-bold" style="font-size: 40px;">
                    <span id="currentQuestionNumber">1</span> / {{ $totalQuestions }}
                </div>
                <div class="mx-auto mt-2" style="width: 220px; max-width: 100%;">
                    <div class="progress" style="height: 6px; border-radius: 999px; background-color: #e8eefc;">
                        <div
                            id="questionProgressBar"
                            class="progress-bar"
                            role="progressbar"
                            style="width: 0%; border-radius: 999px;"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                        ></div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-outline-primary btn-lg px-4">
                    <i class="bi bi-send-check me-2"></i>Nộp bài
                </button>
            </div>
        </div>

        <div class="pt-4">
            @forelse($questions as $index => $question)
                @php
                    $questionType = (int) ($question->type ?? 0);
                @endphp
                <div class="question-panel {{ $loop->first ? '' : 'd-none' }}" data-index="{{ $index }}">
                    <div class="border rounded-4 p-3 p-lg-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fs-5 fw-bold">Câu {{ $index + 1 }}.</div>
                            <span class="badge text-bg-light">Câu {{ $index + 1 }} / {{ $totalQuestions }}</span>
                        </div>

                        <div class="question-content fs-5 lh-lg mb-4">
                            {!! $question->content !!}
                        </div>

                        <div class="mb-3">
                            @if($questionType === 1)
                                <label class="form-label fw-semibold">Chọn các đáp án đúng (có thể chọn nhiều phương án)</label>
                                <div class="row g-2">
                                    @foreach(['A', 'B', 'C', 'D'] as $option)
                                        <div class="col-6 col-md-3">
                                            <label class="form-check border rounded-3 p-3 h-100 d-flex align-items-center gap-2">
                                                <input class="form-check-input m-0" type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option }}">
                                                <span class="fw-semibold">{{ $option }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($questionType === 2)
                                <label class="form-label fw-semibold">Chọn Đúng hoặc Sai</label>
                                <div class="row g-2">
                                    @foreach(['Đúng', 'Sai'] as $option)
                                        <div class="col-6 col-md-3">
                                            <label class="form-check border rounded-3 p-3 h-100 d-flex align-items-center gap-2">
                                                <input class="form-check-input m-0" type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}">
                                                <span class="fw-semibold">{{ $option }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <label class="form-label fw-semibold">Chọn một đáp án đúng</label>
                                <div class="row g-2">
                                    @foreach(['A', 'B', 'C', 'D'] as $option)
                                        <div class="col-6 col-md-3">
                                            <label class="form-check border rounded-3 p-3 h-100 d-flex align-items-center gap-2">
                                                <input class="form-check-input m-0" type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}">
                                                <span class="fw-semibold">{{ $option }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-warning mb-0">Chưa có câu hỏi để làm bài kiểm tra đầu vào.</div>
            @endforelse
        </div>

        @if($totalQuestions > 0)
            <div class="d-flex justify-content-between align-items-center mt-4">
                <button type="button" class="btn btn-outline-secondary px-4" id="prevQuestion" disabled>
                    <i class="bi bi-arrow-left me-2"></i>Câu trước
                </button>

                <div class="text-muted small" id="questionCounter">Câu 1 / {{ $totalQuestions }}</div>

                <button type="button" class="btn btn-primary px-4" id="nextQuestion">
                    Câu tiếp theo<i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const form = document.getElementById('examForm');
    if (!form) return;

    const examId = parseInt(form.dataset.examId || '0', 10);
    const totalQuestions = parseInt(form.dataset.total || '0', 10);
    const timeLimitMinutes = parseInt(form.dataset.timeLimit || '0', 10);
    const panels = Array.from(document.querySelectorAll('.question-panel'));
    const prevBtn = document.getElementById('prevQuestion');
    const nextBtn = document.getElementById('nextQuestion');
    const counter = document.getElementById('questionCounter');
    const currentQuestionNumber = document.getElementById('currentQuestionNumber');
    const questionProgressBar = document.getElementById('questionProgressBar');
    const startedAtInput = document.getElementById('startedAt');
    const timerText = document.getElementById('timerText');
    const timerTextBig = document.getElementById('timerTextBig');
    window.examLeaveGuard = {
        enabled: true,
        type: 'kiem-tra-dau-vao',
        examId: examId,
        storageKey: `exam_test_session_${examId || 'default'}`,
        resetUrl: @json(route('exam.reset-session')),
    };

    let currentIndex = 0;
    let isSubmitting = false;
    let timerInterval = null;
    const storageKey = `exam_test_session_${examId || 'default'}`;
    const storageFallback = {
        startedAt: Date.now(),
        currentIndex: 0,
        answers: {},
    };
    let state = loadState();
    if (startedAtInput) {
        startedAtInput.value = String(state.startedAt);
    }
    let remainingSeconds = Math.max(0, (timeLimitMinutes * 60) - Math.floor((Date.now() - state.startedAt) / 1000));

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
        const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
        return `${mins}:${secs}`;
    }

    function loadState() {
        if (!window.sessionStorage) {
            return { ...storageFallback };
        }

        if (!examId) {
            return { ...storageFallback };
        }

        const raw = sessionStorage.getItem(storageKey);
        if (!raw) {
            const fresh = { ...storageFallback };
            sessionStorage.setItem(storageKey, JSON.stringify(fresh));
            return fresh;
        }

        try {
            const parsed = JSON.parse(raw);
            return {
                startedAt: typeof parsed.startedAt === 'number' ? parsed.startedAt : Date.now(),
                currentIndex: typeof parsed.currentIndex === 'number' ? parsed.currentIndex : 0,
                answers: parsed.answers && typeof parsed.answers === 'object' ? parsed.answers : {},
            };
        } catch (error) {
            const fresh = { ...storageFallback };
            sessionStorage.setItem(storageKey, JSON.stringify(fresh));
            return fresh;
        }
    }

    function saveState() {
        if (!window.sessionStorage || !examId) {
            return;
        }

        sessionStorage.setItem(storageKey, JSON.stringify(state));
        if (startedAtInput) {
            startedAtInput.value = String(state.startedAt);
        }
    }

    function escapeSelectorValue(value) {
        if (window.CSS && typeof CSS.escape === 'function') {
            return CSS.escape(value);
        }

        return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    }

    function disableExamInputs() {
        form.querySelectorAll('input, button').forEach((el) => {
            el.disabled = true;
        });
    }

    function restoreAnswers() {
        Object.entries(state.answers || {}).forEach(([questionId, answer]) => {
            if (Array.isArray(answer)) {
                answer.forEach((value) => {
                    const input = form.querySelector(`input[name="answers[${questionId}][]"][value="${escapeSelectorValue(value)}"]`);
                    if (input) {
                        input.checked = true;
                    }
                });
                return;
            }

            const input = form.querySelector(`input[name="answers[${questionId}]"][value="${escapeSelectorValue(String(answer))}"]`);
            if (input) {
                input.checked = true;
            }
        });
    }

    function submitExam(isAuto = false) {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        const submitButton = form.querySelector('button[type="submit"]');
        const payload = new FormData(form);
        payload.set('started_at', String(state.startedAt));
        payload.set('auto_submit', isAuto ? '1' : '0');

        disableExamInputs();
        if (prevBtn) prevBtn.disabled = true;
        if (nextBtn) nextBtn.disabled = true;
        if (submitButton) submitButton.disabled = true;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: payload,
        })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Không thể nộp bài, vui lòng thử lại.');
                }

                return data;
            })
            .then((data) => {
                if (window.sessionStorage && examId) {
                    sessionStorage.removeItem(storageKey);
                }

                const correct = Number(data.score || 0);
                const total = Number(data.total_questions || 0);
                const wrong = Math.max(total - correct, 0);
                const percent = total > 0 ? Math.round((correct / total) * 100) : 0;

                Swal.fire({
                    icon: 'success',
                    iconHtml: '<i class="bi bi-trophy-fill" style="font-size: 3rem; color: #ffffff;"></i>',
                    background: '#2563eb',
                    color: '#ffffff',
                    title: 'Nộp bài thành công!',
                    html: `
                        <div style="font-size: 1.05rem; margin-bottom: 1rem;">
                            Bạn đúng <strong>${correct}/${total}</strong> câu
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 1rem;">
                            <div style="background: rgba(255,255,255,.14); border-radius: 16px; padding: 12px 14px;">
                                <div style="font-size: .8rem; opacity: .9;">Đúng</div>
                                <div style="font-size: 1.4rem; font-weight: 800;">${correct}</div>
                            </div>
                            <div style="background: rgba(255,255,255,.14); border-radius: 16px; padding: 12px 14px;">
                                <div style="font-size: .8rem; opacity: .9;">Sai</div>
                                <div style="font-size: 1.4rem; font-weight: 800;">${wrong}</div>
                            </div>
                        </div>
                        <div style="text-align: left; font-size: .9rem; margin-bottom: .4rem;">Điểm phần trăm</div>
                        <div class="progress" style="height: 10px; border-radius: 999px; background: rgba(255,255,255,.22);">
                            <div class="progress-bar bg-warning" style="width: ${percent}%; border-radius: 999px;"></div>
                        </div>
                        <div style="margin-top: .5rem; font-size: .95rem; font-weight: 700;">${percent}% hoàn thành</div>
                    `,
                    confirmButtonText: 'Xem tiếp',
                    confirmButtonColor: '#facc15',
                    backdrop: 'rgba(15, 23, 42, .75)',
                    width: 560,
                }).then(() => {
                    if (window.examLeaveGuard) {
                        window.examLeaveGuard.enabled = false;
                    }
                    window.location.href = data.redirect_url || '/tai-khoan';
                });
            })
            .catch((error) => {
                isSubmitting = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Không thể nộp bài',
                    text: error.message || 'Có lỗi xảy ra khi nộp bài.',
                }).then(() => {
                    window.location.reload();
                });
            });
    }

    function confirmSubmitExam() {
        Swal.fire({
            icon: 'question',
            title: 'Bạn có chắc chắn nộp bài không?',
            showCancelButton: true,
            confirmButtonText: 'Có, nộp bài',
            cancelButtonText: 'Chưa',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
        }).then((result) => {
            if (result.isConfirmed) {
                submitExam(false);
            }
        });
    }

    function render() {
        panels.forEach((panel, index) => {
            panel.classList.toggle('d-none', index !== currentIndex);
        });

        if (prevBtn) {
            prevBtn.disabled = currentIndex === 0;
        }

        if (nextBtn) {
            const isLastQuestion = currentIndex === totalQuestions - 1;
            nextBtn.textContent = isLastQuestion ? 'Nộp bài' : 'Câu tiếp theo';
            nextBtn.innerHTML = isLastQuestion
                ? 'Nộp bài<i class="bi bi-send-check ms-2"></i>'
                : 'Câu tiếp theo<i class="bi bi-arrow-right ms-2"></i>';
        }

        if (counter) {
            counter.textContent = `Câu ${currentIndex + 1} / ${totalQuestions}`;
        }

        if (currentQuestionNumber) {
            currentQuestionNumber.textContent = currentIndex + 1;
        }

        if (questionProgressBar) {
            const percent = totalQuestions > 0 ? Math.round(((currentIndex + 1) / totalQuestions) * 100) : 0;
            questionProgressBar.style.width = `${percent}%`;
            questionProgressBar.setAttribute('aria-valuenow', percent.toString());
        }

        state.currentIndex = currentIndex;
        saveState();
    }

    function updateTimer() {
        const time = formatTime(remainingSeconds);
        if (timerTextBig) timerTextBig.textContent = time;
    }

    if (totalQuestions > 0) {
        currentIndex = Math.min(Math.max(state.currentIndex || 0, 0), totalQuestions - 1);
        render();
        restoreAnswers();
        updateTimer();

        form.addEventListener('change', function (event) {
            const target = event.target;
            if (!target || !target.name) {
                return;
            }

            const match = target.name.match(/^answers\[(\d+)\](\[\])?$/);
            if (!match) {
                return;
            }

            const questionId = match[1];
            if (match[2]) {
                const checkedValues = Array.from(
                    form.querySelectorAll(`input[name="answers[${questionId}][]"]:checked`)
                ).map((el) => el.value);

                if (checkedValues.length > 0) {
                    state.answers[questionId] = checkedValues;
                } else {
                    delete state.answers[questionId];
                }
            } else {
                const checked = form.querySelector(`input[name="answers[${questionId}]"]:checked`);
                if (checked) {
                    state.answers[questionId] = checked.value;
                } else {
                    delete state.answers[questionId];
                }
            }

            saveState();
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                if (currentIndex > 0) {
                    currentIndex--;
                    render();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                if (currentIndex < totalQuestions - 1) {
                    currentIndex++;
                    render();
                } else {
                    confirmSubmitExam();
                }
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            confirmSubmitExam();
        });

        timerInterval = setInterval(function () {
            if (remainingSeconds <= 0) {
                return;
            }

            remainingSeconds--;
            updateTimer();

            if (remainingSeconds <= 0) {
                clearInterval(timerInterval);
                submitExam(true);
            }
        }, 1000);

        if (remainingSeconds <= 0) {
            clearInterval(timerInterval);
            submitExam(true);
        }
    }
})();
</script>
@endsection
