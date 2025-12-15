{{-- resources/views/cpd/sessions/show.blade.php --}}
@extends('layouts.cpd')

@section('title', $session->course->title . ' – CPD session')

@section('content')
@php
    $course = $session->course;
    $domain = $course->domain ?? null;

    $start  = optional($session->start_date)->format('d M Y');
    $end    = optional($session->end_date)->format('d M Y');
    $dates  = $start && $end ? "{$start} – {$end}" : ($start ?? 'TBC');

    $modeLabel = match($session->delivery_mode) {
        'online'       => 'Online (Setlhare)',
        'face_to_face' => 'Face-to-face',
        'hybrid'       => 'Hybrid (online + campus)',
        default        => ucfirst(str_replace('_',' ', (string) $session->delivery_mode)),
    };

    $fee = $session->price ?? $course->default_price;
    $curr = $session->currency ?? $course->currency ?? 'BWP';
@endphp

<div class="page-wrap">
    <div class="container" style="max-width: 1120px;">
        {{-- Back link --}}
        <div style="margin-bottom: 12px;">
            <a href="{{ route('cpd.sessions.index') }}"
               style="font-size:13px; color:#6b7280; text-decoration:none;">
                ← Back to CPD course list
            </a>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:24px;">
            {{-- LEFT: course overview --}}
            <div style="flex:1 1 0; min-width:260px;">
                <h1 style="margin:0 0 4px; font-size:26px; font-weight:700;">
                    {{ $course->title }}
                </h1>

                @if($domain)
                    <div style="font-size:13px; color:#6b7280; margin-bottom:8px;">
                        {{ $domain->name }}
                    </div>
                @endif

                <p style="font-size:14px; color:#374151; line-height:1.5; margin-bottom:12px;">
                    {{ $course->short_description ?? 'This CPD programme builds practical, job-ready skills through IDM’s industry-recognised training.' }}
                </p>

                <h3 style="font-size:15px; font-weight:600; margin-top:16px;">What you will gain</h3>
                <ul style="font-size:13px; color:#4b5563; padding-left:18px; margin-bottom:0;">
                    <li>Updated skills aligned to Botswana and SADC context</li>
                    <li>Practical tools and templates you can apply immediately</li>
                    <li>Engagement with IDM facilitators and peers</li>
                </ul>
            </div>

            {{-- RIGHT: session summary + CTA --}}
            <div style="
                flex:0 0 320px;
                max-width:340px;
                width:100%;
                background:#ffffff;
                border:1px solid #e5e7eb;
                border-radius:12px;
                padding:16px 18px;
                box-shadow:0 10px 25px rgba(15,23,42,0.06);
                font-size:14px;
            ">
                <div style="font-size:15px; font-weight:600; margin-bottom:10px;">
                    Upcoming intake details
                </div>

                <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:12px; color:#374151;">
                    <div><strong>Dates:</strong> {{ $dates }}</div>
                    <div><strong>Mode:</strong> {{ $modeLabel }}</div>
                    <div><strong>Location:</strong> {{ $session->location ?? 'Online' }}</div>
                    @if($fee)
                        <div><strong>Fee:</strong> {{ $curr }} {{ number_format($fee, 2) }}</div>
                    @endif
                    @if($course->duration_days)
                        <div><strong>Duration:</strong> {{ $course->duration_days }} day{{ $course->duration_days > 1 ? 's' : '' }}</div>
                    @endif
                </div>

                <div style="font-size:12px; color:#6b7280; margin-bottom:8px;">
                    Step 1 of 3 – Choose your intake
                </div>

                <a href="{{ route('cpd.sessions.register', $session) }}"
                   style="
                        display:block;
                        text-align:center;
                        margin-top:4px;
                        padding:10px 12px;
                        border-radius:999px;
                        background:#16a34a;
                        color:#ffffff;
                        font-weight:600;
                        text-decoration:none;
                        font-size:14px;
                   ">
                    Register for this CPD run
                </a>

                <p style="font-size:12px; color:#6b7280; margin-top:8px;">
                    Next: you’ll confirm your details and complete payment.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
