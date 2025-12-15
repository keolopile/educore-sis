{{-- resources/views/cpd/sessions/index.blade.php --}}
@extends('layouts.cpd')

@section('title', 'Professional Development Courses')

@section('content')
<style>
    .cpd-hero {
        padding: 32px 0 20px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 20px;
    }
    .cpd-hero h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .cpd-hero p {
        margin: 0;
        color: #4b5563;
    }

    .cpd-filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        align-items: center;
    }
    .cpd-filter-bar label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .cpd-course-card {
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 16px 16px 14px;
    }
    .cpd-card-header {
        margin-bottom: 10px;
    }
    .cpd-card-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .cpd-card-subtitle {
        font-size: 12px;
        font-weight: 600;
        color: #0f766e;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: 6px;
    }
    .cpd-card-body {
        font-size: 13px;
        color: #4b5563;
        margin-bottom: 10px;
    }
    .cpd-meta-list {
        list-style: none;
        padding: 0;
        margin: 0 0 8px;
        font-size: 12px;
        color: #374151;
    }
    .cpd-meta-list li {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 2px;
    }
    .cpd-meta-dot {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #10b981;
        flex-shrink: 0;
    }
    .cpd-price-main {
        font-weight: 700;
        font-size: 18px;
        color: #047857;
        margin-bottom: 2px;
    }
    .cpd-price-alt {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .cpd-card-footer {
        margin-top: auto;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .cpd-badge-enrolled {
        font-size: 11px;
        background: #fef9c3;
        color: #92400e;
        padding: 3px 8px;
        border-radius: 999px;
        align-self: flex-start;
    }
    @media (max-width: 768px) {
        .cpd-hero h1 {
            font-size: 22px;
        }
    }
</style>

<div class="cpd-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1>Professional Development Courses</h1>
            <p>Enhance your skills with IDM’s industry-recognised short courses and CPD programmes.</p>
        </div>
        <div class="text-end small text-muted">
            <div><strong>{{ $sessions->count() }}</strong> upcoming intakes</div>
            <div>Gaborone • Francistown • Online (Setlhare)</div>
        </div>
    </div>
</div>

{{-- Simple filter bar (you can wire this up later to real filters) --}}
<form method="GET" class="cpd-filter-bar">
    <div>
        <label for="domain">Domain</label>
        <select name="domain" id="domain" class="form-select form-select-sm">
            <option value="">All domains</option>
            {{-- later: loop domains --}}
        </select>
    </div>

    <div>
        <label for="mode">Mode</label>
        <select name="mode" id="mode" class="form-select form-select-sm">
            <option value="">Any mode</option>
            <option value="online">Online</option>
            <option value="face_to_face">Face-to-face</option>
            <option value="hybrid">Hybrid</option>
        </select>
    </div>

    <div>
        <label for="month">Month</label>
        <select name="month" id="month" class="form-select form-select-sm">
            <option value="">Any month</option>
            {{-- you can fill with upcoming months --}}
        </select>
    </div>

    <div class="ms-auto">
        <button type="submit" class="btn btn-sm btn-outline-secondary">
            Filter
        </button>
    </div>
</form>

@if($sessions->isEmpty())
    <div class="alert alert-info">
        No upcoming intakes are currently open. Please check again soon or contact IDM CCPD.
    </div>
@else
    <div class="row g-4">
        @foreach($sessions as $session)
            @php
                $course = $session->course;
                $domain = $course->domain->name ?? null;

                $start = optional($session->start_date)->format('d M Y');
                $end   = optional($session->end_date)->format('d M Y');

                $durationDays = $course->duration_days
                    ?? $session->duration_days
                    ?? null;

                $price  = $session->price ?? $course->default_price;
                $curr   = $session->currency ?? $course->currency ?? 'BWP';

                $deliveryLabel = match($session->delivery_mode) {
                    'online'       => 'Online (Setlhare)',
                    'face_to_face' => 'Face-to-face',
                    'hybrid'       => 'Hybrid (online + campus)',
                    default        => ucfirst($session->delivery_mode),
                };
            @endphp

            <div class="col-md-4">
                <div class="cpd-course-card">
                    <div class="cpd-card-header">
                        @if($domain)
                            <div class="cpd-card-subtitle">{{ $domain }}</div>
                        @endif>
                        <div class="cpd-card-title">
                            {{ $course->title }}
                        </div>
                    </div>

                    <div class="cpd-card-body">
                        <p class="mb-2">
                            {{ Str::limit($course->summary ?? 'This comprehensive programme covers all essential aspects with practical, real-world applications.', 140) }}
                        </p>

                        <ul class="cpd-meta-list">
                            @if($durationDays)
                                <li>
                                    <span class="cpd-meta-dot"></span>
                                    Duration: {{ $durationDays }} day{{ $durationDays > 1 ? 's' : '' }}
                                </li>
                            @endif
                            <li>
                                <span class="cpd-meta-dot"></span>
                                Mode: {{ $deliveryLabel }}
                            </li>
                            <li>
                                <span class="cpd-meta-dot"></span>
                                Next intake: {{ $start ?: 'TBA' }}
                                @if($end && $end !== $start)
                                    – {{ $end }}
                                @endif
                            </li>
                            @if($session->location)
                                <li>
                                    <span class="cpd-meta-dot"></span>
                                    Location: {{ $session->location }}
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="cpd-card-footer">
                        @if($price)
                            <div>
                                <div class="cpd-price-main">
                                    {{ $curr }} {{ number_format($price, 2) }}
                                </div>
                                <div class="cpd-price-alt">
                                    or 3 monthly payments of {{ $curr }} {{ number_format($price / 3, 2) }}
                                </div>
                            </div>
                        @endif

                        {{-- Later: real number of enrolled --}}
                        <span class="cpd-badge-enrolled">
                            🔰 Popular • intakes filling fast
                        </span>

                        <a href="{{ route('cpd.sessions.show', $session) }}"
                           class="btn btn-success btn-sm w-100 mt-1">
                            Enroll Now
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
