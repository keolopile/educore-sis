@extends('layouts.cpd')

@section('title', $course->title . ' – Learning')

@section('content')
<div class="page-wrap">
    <div class="container" style="display:flex; gap:24px; align-items:flex-start;">

        {{-- LEFT: modules + lessons --}}
        <aside style="width:260px; border-right:1px solid #e5e7eb; padding-right:16px;">
            <h2 style="font-size:18px; font-weight:600; margin-bottom:8px;">
                {{ $course->title }}
            </h2>
            <p style="font-size:12px; color:#6b7280; margin-bottom:16px;">
                Learning path – modules & video lessons
            </p>

            @foreach($modules as $module)
                <div style="margin-bottom:12px;">
                    <div style="font-weight:600; font-size:13px; margin-bottom:4px;">
                        {{ $module->position }}. {{ $module->title }}
                    </div>

                    @forelse($module->lessons as $lesson)
                        @php
                            $isCurrent = $lesson->id === $currentLesson->id;
                        @endphp
                        <a href="{{ route('cpd.learn.show', ['course' => $course->id, 'lesson' => $lesson->id]) }}"
                           style="
                                display:block;
                                padding:6px 8px;
                                font-size:13px;
                                border-radius:6px;
                                margin-bottom:2px;
                                text-decoration:none;
                                color: {{ $isCurrent ? '#065f46' : '#374151' }};
                                background: {{ $isCurrent ? '#d1fae5' : 'transparent' }};
                           ">
                            {{ $lesson->position }}. {{ $lesson->title }}
                        </a>
                    @empty
                        <div style="font-size:12px; color:#9ca3af;">
                            No lessons yet.
                        </div>
                    @endforelse
                </div>
            @endforeach
        </aside>

        {{-- RIGHT: video + details --}}
        <main style="flex:1;">
            @if(session('status'))
                <div style="
                    background:#ecfdf3;
                    border:1px solid #bbf7d0;
                    color:#166534;
                    padding:8px 10px;
                    border-radius:6px;
                    font-size:13px;
                    margin-bottom:12px;
                ">
                    {{ session('status') }}
                </div>
            @endif

            <h1 style="font-size:20px; font-weight:600; margin-bottom:8px;">
                {{ $currentLesson->title }}
            </h1>

            <p style="font-size:12px; color:#6b7280; margin-bottom:12px;">
                Module: {{ $currentLesson->module->title ?? '-' }}
                @if($currentLesson->duration_seconds)
                    · {{ floor($currentLesson->duration_seconds / 60) }} min
                @endif
            </p>

            {{-- VIDEO AREA --}}
            <div style="background:#000; border-radius:8px; overflow:hidden; margin-bottom:16px;">
                @php
                    $provider = $currentLesson->video_provider;
                    $ref      = $currentLesson->video_reference;
                @endphp

                @if($provider === 'youtube')
                    <iframe width="100%" height="420"
                            src="https://www.youtube.com/embed/{{ $ref }}"
                            title="YouTube video player"
                            frameborder="0"
                            allowfullscreen></iframe>

                @elseif($provider === 'vimeo')
                    <iframe src="https://player.vimeo.com/video/{{ $ref }}"
                            width="100%" height="420" frameborder="0"
                            allow="autoplay; fullscreen; picture-in-picture"
                            allowfullscreen></iframe>

                @elseif($provider === 'file')
                    <video controls width="100%" height="420">
                        <source src="{{ asset('storage/'.$ref) }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>

                @else
                    {{-- Treat as full video URL (YouTube, Vimeo or MP4 link) --}}
                    @if(Str::endsWith(strtolower($ref), ['.mp4', '.webm', '.ogg']))
                        <video controls width="100%" height="420">
                            <source src="{{ $ref }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    @else
                        <iframe width="100%" height="420"
                                src="{{ $ref }}"
                                frameborder="0"
                                allowfullscreen></iframe>
                    @endif
                @endif
            </div>

            {{-- Lesson description --}}
            @if($currentLesson->description)
                <p style="font-size:14px; color:#374151; margin-bottom:16px;">
                    {{ $currentLesson->description }}
                </p>
            @endif

            {{-- Progress --}}
            <div style="display:flex; align-items:center; gap:12px;">
                <form method="POST" action="{{ route('cpd.lessons.progress', $currentLesson->id) }}">
                    @csrf
                    <input type="hidden" name="completed" value="1">
                    <button type="submit" class="btn btn-primary">
                        {{ optional($progress)->completed ? 'Completed ✔' : 'Mark lesson as completed' }}
                    </button>
                </form>

                @if($progress && $progress->completed)
                    <span style="font-size:12px; color:#16a34a;">
                        You completed this lesson on {{ $progress->updated_at->format('d M Y H:i') }}.
                    </span>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection
