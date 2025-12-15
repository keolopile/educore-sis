{{-- resources/views/cpd/courses/learn.blade.php --}}
@extends('layouts.cpd')

@section('title', ($course->title ?? 'CPD Course') . ' – ' . ($lesson->title ?? 'Lesson'))

@section('content')
@php
    /** @var \App\Models\CpdCourse $course */
    /** @var \App\Models\CpdLesson $lesson */
    /** @var \App\Models\CpdLessonProgress|null $progress */
    /** @var \Illuminate\Support\Collection|\App\Models\CpdQuiz[] $quizzes */

    $modules = $course->modules ?? collect();

    // ---- Video embed URL normalisation ----
    $iframeSrc = $lesson->video_reference;
    $isYoutube = $lesson->video_provider === 'youtube' || str_contains($iframeSrc, 'youtu');

    if ($isYoutube) {
        // normalise a few common YouTube URL shapes to embed + enablejsapi
        if (str_contains($iframeSrc, 'watch?v=')) {
            $videoId = explode('watch?v=', $iframeSrc)[1];
            $videoId = strtok($videoId, '&');
        } elseif (str_contains($iframeSrc, 'youtu.be/')) {
            $videoId = explode('youtu.be/', $iframeSrc)[1];
            $videoId = strtok($videoId, '?');
        } elseif (str_contains($iframeSrc, '/embed/')) {
            $videoId = explode('/embed/', $iframeSrc)[1];
            $videoId = strtok($videoId, '?');
        } else {
            $videoId = $iframeSrc; // fallback
        }

        $iframeSrc = 'https://www.youtube.com/embed/' . $videoId . '?enablejsapi=1&rel=0';
    }

    // ---- Quiz payload for JS ----
    $quizCollection = $quizzes ?? collect();

    $quizPayload = $quizCollection->map(function ($quiz) {
        return [
            'id'       => $quiz->id,
            'time'     => (int) $quiz->time_offset_seconds,
            'title'    => $quiz->title,
            'question' => $quiz->question,
            'type'     => $quiz->type,
            'required' => (bool) $quiz->required_to_continue,
            'options'  => $quiz->options->map(fn($o) => [
                'id'         => $o->id,
                'label'      => $o->label,
                'text'       => $o->option_text,
                'is_correct' => (bool) $o->is_correct,
            ])->values(),
        ];
    })->values();
@endphp

<div class="page-wrap">
    <div class="container" style="max-width:1200px; margin:0 auto; padding:16px 0;">

        {{-- Breadcrumb / heading --}}
        <div style="margin-bottom:12px;">
            <a href="{{ route('cpd.courses.show', $course) }}"
               style="font-size:13px; color:#4b5563; text-decoration:none;">
                ← Back to course overview
            </a>
        </div>

        <div style="display:flex; gap:20px; align-items:flex-start;">

            {{-- LEFT: modules & lessons nav --}}
            <aside style="width:280px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:12px;">
                <div style="font-size:14px; font-weight:600; margin-bottom:4px;">
                    {{ $course->title ?? 'CPD Course' }} <span style="font-size:11px; font-weight:400;">(MS Excel)</span>
                </div>
                <div style="font-size:11px; color:#6b7280; margin-bottom:10px;">
                    Learning path – modules & video lessons
                </div>

                @forelse($modules as $mIndex => $module)
                    <div style="margin-bottom:10px;">
                        <div style="font-size:13px; font-weight:600; color:#111827;">
                            {{ $mIndex + 1 }}. {{ $module->title }}
                        </div>

                        @php
                            $moduleLessons = $module->lessons ?? collect();
                        @endphp

                        @if($moduleLessons->isEmpty())
                            <div style="font-size:11px; color:#9ca3af;">No lessons yet.</div>
                        @else
                            <ul style="list-style:none; padding-left:12px; margin:4px 0;">
                                @foreach($moduleLessons as $lIndex => $modLesson)
                                    @php
                                        $isActive = $modLesson->id === $lesson->id;
                                        $url = route('cpd.courses.learn', [$course, $modLesson]);
                                    @endphp
                                    <li style="margin-bottom:2px;">
                                        <a href="{{ $url }}"
                                           style="
                                                display:block;
                                                font-size:12px;
                                                padding:4px 6px;
                                                border-radius:4px;
                                                text-decoration:none;
                                                color:{{ $isActive ? '#0f172a' : '#374151' }};
                                                background:{{ $isActive ? '#dbeafe' : 'transparent' }};
                                            ">
                                            {{ $modLesson->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @empty
                    <div style="font-size:12px; color:#9ca3af;">No modules defined yet.</div>
                @endforelse
            </aside>

            {{-- RIGHT: active lesson --}}
            <main style="flex:1; border:1px solid #e5e7eb; border-radius:8px; padding:16px; background:#ffffff;">

                <div style="font-size:15px; font-weight:600;">
                    {{ $lesson->title }}
                </div>
                <div style="font-size:11px; color:#6b7280; margin-bottom:8px;">
                    Module: {{ $lesson->module->title ?? '—' }}
                    @if($lesson->duration_seconds)
                        · {{ floor($lesson->duration_seconds / 60) }} min
                    @endif
                </div>

                {{-- Video player --}}
                <div id="lesson-player-wrapper" style="margin-bottom:10px;">
                    <div id="lesson-player-container" style="position:relative; padding-top:56.25%; background:#000;">
                        <iframe
                            id="lesson-player"
                            src="{{ $iframeSrc }}"
                            style="position:absolute; top:0; left:0; width:100%; height:100%;"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>

                <div id="lesson-completed-banner"
                     class="{{ optional($progress)->completed ? '' : 'hidden' }}"
                     style="margin-bottom:8px; font-size:13px; color:#15803d;">
                    ✅ Lesson marked as completed
                </div>

                @if($lesson->description)
                    <div style="font-size:13px; color:#4b5563; margin-top:6px;">
                        {!! nl2br(e($lesson->description)) !!}
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>

{{-- Quiz modal --}}
<div id="quiz-modal" class="hidden"
     style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:50;">
    <div style="background:white; padding:20px; max-width:480px; width:100%; border-radius:8px;">
        <h3 id="quiz-title" style="margin-top:0; font-size:16px;"></h3>
        <p id="quiz-question" style="font-size:13px;"></p>
        <div id="quiz-options" style="margin-top:8px;"></div>
        <div id="quiz-feedback" style="margin-top:8px; font-size:13px;"></div>
        <button id="quiz-submit-btn" class="btn btn-primary" style="margin-top:10px;">
            Submit
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://www.youtube.com/iframe_api"></script>
<script>
    // ------------------- CONFIG FROM BACKEND -------------------
    const LESSON_ID     = @json($lesson->id);
    const COURSE_ID     = @json($course->id);
    const DURATION_META = @json($lesson->duration_seconds ?? null);
    const LAST_POSITION = @json(optional($progress)->last_position_seconds ?? 0);
    const PROGRESS_URL  = @json(route('cpd.lessons.progress', $lesson));
    const CSRF_TOKEN    = @json(csrf_token());
    const QUIZZES       = @json($quizPayload);

    // ------------------- STATE -------------------
    let player;
    let watchInterval  = null;
    let maxWatched     = {{ (int) (optional($progress)->seconds_watched ?? 0) }};
    let shownQuizIds   = new Set();
    let pendingQuiz    = null;

    // YouTube IFrame API callback
    function onYouTubeIframeAPIReady() {
        player = new YT.Player('lesson-player', {
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange
            }
        });
    }

    function onPlayerReady(event) {
        // Resume from last saved position if we have one
        if (LAST_POSITION && LAST_POSITION > 5) {
            player.seekTo(LAST_POSITION, true);
        }

        // Poll for quiz triggers every second
        setInterval(checkForQuizTrigger, 1000);
    }

    function onPlayerStateChange(event) {
        if (event.data === YT.PlayerState.PLAYING) {
            startTracking();
        } else if (event.data === YT.PlayerState.PAUSED ||
                   event.data === YT.PlayerState.ENDED) {
            stopTracking();
            saveProgress(event.data === YT.PlayerState.ENDED);
        }
    }

    function startTracking() {
        if (watchInterval) return;

        watchInterval = setInterval(() => {
            if (!player || typeof player.getCurrentTime !== 'function') return;

            const current = Math.floor(player.getCurrentTime());
            maxWatched = Math.max(maxWatched, current);
        }, 2000); // every 2 seconds
    }

    function stopTracking() {
        if (watchInterval) {
            clearInterval(watchInterval);
            watchInterval = null;
        }
    }

    function saveProgress(forceCompleted = false) {
        if (!player || typeof player.getCurrentTime !== 'function') return;

        const current  = Math.floor(player.getCurrentTime());
        const duration = DURATION_META || Math.floor(player.getDuration() || 0);

        let completed = forceCompleted;
        if (!completed && duration > 0) {
            // mark completed if user watched at least 90%
            completed = (maxWatched >= duration * 0.9);
        }

        fetch(PROGRESS_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                seconds_watched: maxWatched,
                last_position_seconds: current,
                completed: completed ? 1 : 0,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.completed) {
                const banner = document.getElementById('lesson-completed-banner');
                if (banner) banner.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error('Save progress error', err);
        });
    }

    // periodic background save
    setInterval(() => saveProgress(false), 10000);

    // ------------------- QUIZ TIMING -------------------
    function checkForQuizTrigger() {
        if (!player || typeof player.getCurrentTime !== 'function') return;
        if (!Array.isArray(QUIZZES) || QUIZZES.length === 0) return;

        const current = Math.floor(player.getCurrentTime());

        for (const quiz of QUIZZES) {
            if (shownQuizIds.has(quiz.id)) continue;

            // trigger within small window when we cross quiz.time
            if (current >= quiz.time && current <= quiz.time + 2) {
                showQuiz(quiz);
                break;
            }
        }
    }

    function showQuiz(quiz) {
        pendingQuiz = quiz;
        shownQuizIds.add(quiz.id);

        stopTracking();
        if (player && typeof player.pauseVideo === 'function') {
            player.pauseVideo();
        }

        const modal      = document.getElementById('quiz-modal');
        const titleEl    = document.getElementById('quiz-title');
        const questionEl = document.getElementById('quiz-question');
        const optionsEl  = document.getElementById('quiz-options');
        const feedbackEl = document.getElementById('quiz-feedback');

        titleEl.textContent    = quiz.title;
        questionEl.textContent = quiz.question;
        optionsEl.innerHTML    = '';
        feedbackEl.textContent = '';

        quiz.options.forEach(opt => {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <label style="display:flex; gap:6px; cursor:pointer; margin-bottom:4px;">
                    <input type="radio" name="quiz-${quiz.id}" value="${opt.id}" />
                    <span>${opt.label}. ${opt.text}</span>
                </label>
            `;
            optionsEl.appendChild(wrapper);
        });

        modal.classList.remove('hidden');
    }

    // Quiz submit handler
    document.getElementById('quiz-submit-btn').addEventListener('click', () => {
        if (!pendingQuiz) return;

        const selected = document.querySelector(`input[name="quiz-${pendingQuiz.id}"]:checked`);
        const feedbackEl = document.getElementById('quiz-feedback');

        if (!selected) {
            feedbackEl.textContent = 'Please choose an answer.';
            feedbackEl.style.color = '#b91c1c';
            return;
        }

        const optId  = parseInt(selected.value, 10);
        const chosen = pendingQuiz.options.find(o => o.id === optId);

        if (chosen && chosen.is_correct) {
            feedbackEl.textContent = 'Correct! Great job.';
            feedbackEl.style.color = '#15803d';

            setTimeout(() => {
                document.getElementById('quiz-modal').classList.add('hidden');
                pendingQuiz = null;
                startTracking();
                if (player && typeof player.playVideo === 'function') {
                    player.playVideo();
                }
                // TODO: send quiz attempt to backend
            }, 800);
        } else {
            feedbackEl.textContent = 'Not quite. Try again.';
            feedbackEl.style.color = '#b91c1c';

            if (!pendingQuiz.required) {
                // allow continuing even if wrong
                setTimeout(() => {
                    document.getElementById('quiz-modal').classList.add('hidden');
                    pendingQuiz = null;
                    startTracking();
                    if (player && typeof player.playVideo === 'function') {
                        player.playVideo();
                    }
                }, 1000);
            }
        }
    });
</script>
@endpush
