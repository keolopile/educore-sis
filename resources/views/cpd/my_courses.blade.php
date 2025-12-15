{{-- resources/views/cpd/my_courses.blade.php --}}
@extends('layouts.cpd')

@section('title', 'My CPD courses')

@section('content')
<style>
    .cpd-my-wrap {
        padding-top: 24px;
    }
    .cpd-my-header h1 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .cpd-my-header p {
        margin: 0;
        color: #4b5563;
    }

    .cpd-course-card {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        height: 100%;
        font-size: 13px;
    }
    .cpd-course-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .cpd-course-domain {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #0f766e;
        margin-bottom: 6px;
        font-weight: 600;
    }
    .cpd-course-meta {
        list-style:none;
        padding:0;
        margin:0 0 8px;
        color:#374151;
    }
    .cpd-course-meta li {
        margin-bottom: 2px;
    }
    .cpd-status-pill {
        display:inline-block;
        padding: 2px 8px;
        border-radius:999px;
        font-size:11px;
        font-weight:500;
        margin-bottom:6px;
    }
    .cpd-status-active {
        background:#dcfce7;
        color:#166534;
    }
    .cpd-status-pending {
        background:#fef3c7;
        color:#92400e;
    }
    .cpd-status-completed {
        background:#dbeafe;
        color:#1d4ed8;
    }
</style>

<div class="cpd-my-wrap">
    <div class="container" style="max-width: 1100px;">
        <div class="d-flex justify-content-between align-items-center mb-3 cpd-my-header">
            <div>
                <h1>My CPD courses</h1>
                <p>Courses you’re enrolled in using this account ({{ auth()->user()->email }}).</p>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if($enrolments->isEmpty())
            <div class="alert alert-info">
                You don’t have any active CPD course enrolments yet.
                Visit the <a href="{{ route('cpd.sessions.index') }}">course catalogue</a>
                to register.
            </div>
        @else
            <div class="row g-4">
                @foreach($enrolments as $enrolment)
                    @php
                        $session = $enrolment->session;
                        $course  = $session->course;
                        $domain  = $course->domain->name ?? null;

                        $status  = $enrolment->enrolment_status; // pending / active / completed
                        $statusLabel = ucfirst($status);
                        $statusClass = match ($status) {
                            'active'    => 'cpd-status-active',
                            'completed' => 'cpd-status-completed',
                            default     => 'cpd-status-pending',
                        };

                        $start = optional($session->start_date)->format('d M Y');
                        $end   = optional($session->end_date)->format('d M Y');
                        $mode  = match($session->delivery_mode) {
                            'online'       => 'Online (Setlhare)',
                            'face_to_face' => 'Face-to-face',
                            'hybrid'       => 'Hybrid (online + campus)',
                            default        => ucfirst($session->delivery_mode),
                        };
                    @endphp

                    <div class="col-md-4">
                        <div class="cpd-course-card">
                            @if($domain)
                                <div class="cpd-course-domain">{{ $domain }}</div>
                            @endif

                            <div class="cpd-course-title">{{ $course->title }}</div>

                            <span class="cpd-status-pill {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>

                            <ul class="cpd-course-meta">
                                <li><strong>Mode:</strong> {{ $mode }}</li>
                                <li>
                                    <strong>Dates:</strong>
                                    {{ $start ?: 'TBA' }}
                                    @if($end && $end !== $start)
                                        – {{ $end }}
                                    @endif
                                </li>
                                @if($session->location)
                                    <li><strong>Location:</strong> {{ $session->location }}</li>
                                @endif
                            </ul>

                            <div class="mt-auto d-flex flex-column gap-1">
                                <a href="{{ route('cpd.learn.show', ['course' => $course->id]) }}"
                                   class="btn btn-success btn-sm">
                                    {{ $status === 'completed' ? 'Review course' : 'Continue learning' }}
                                </a>

                                <a href="{{ route('cpd.sessions.show', $session) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    View session details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
