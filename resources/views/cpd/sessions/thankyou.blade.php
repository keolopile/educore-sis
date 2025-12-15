{{-- resources/views/cpd/sessions/thankyou.blade.php --}}
@extends('layouts.cpd')

@section('title', 'Thank you for registering')

@section('content')
<div class="page-wrap">
    <div class="container" style="max-width: 1100px;">

        @if (session('status'))
            <div class="alert alert-success mb-3">
                {{ session('status') }}
            </div>
        @endif

        <div class="alert alert-success mb-3">
            Payment successful! You have been enrolled in this CPD course.
        </div>

        {{-- Course summary --}}
        <div class="card mb-3">
            <div class="card-body">
                <h1 class="h5 mb-2">Thank you for registering</h1>
                <p class="small text-muted mb-3">
                    Your registration for the CPD course below has been recorded.
                    A confirmation email may be sent to you by IDM CCPD in due course.
                </p>

                <div style="font-size: 13px;">
                    <div style="font-weight:600; margin-bottom:4px;">
                        {{ $session->course->title ?? 'CPD Course' }}
                    </div>
                    <div><strong>Domain:</strong> {{ $session->course->domain->name ?? '-' }}</div>
                    <div>
                        <strong>Dates:</strong>
                        {{ optional($session->start_date)->format('d M Y') }}
                        @if($session->end_date)
                            – {{ optional($session->end_date)->format('d M Y') }}
                        @endif
                    </div>
                    <div><strong>Mode:</strong> {{ ucfirst(str_replace('_',' ', $session->delivery_mode)) }}</div>
                    <div><strong>Location:</strong> {{ $session->location ?? '-' }}</div>
                    <div>
                        <strong>Fee:</strong>
                        @php
                            $price = $session->price ?? $session->course->default_price;
                            $curr  = $session->currency ?? $session->course->currency ?? 'BWP';
                        @endphp
                        @if($price)
                            {{ $curr }} {{ number_format($price, 2) }}
                        @else
                            –
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $course      = $session->course;
            // This route chooses the first lesson if none is given
            $learningUrl = route('cpd.learn.show', $course);
        @endphp

        {{-- Start learning CTA --}}
        <div class="card border-success mb-3" style="background:#ecfdf3;">
            <div class="card-body">
                <h2 class="h6 text-success mb-2">Access your online class</h2>
                <p class="small text-muted mb-3">
                    Your payment and registration are complete. You can now join the
                    online class hosted on IDM’s CPD learning platform.
                </p>

                <a href="{{ $learningUrl }}" class="btn btn-success">
                    Go to my online class
                </a>

                <p class="small text-muted mt-2 mb-0">
                    You can always return later from the CPD course list.
                </p>
            </div>
        </div>

        <a href="{{ route('cpd.sessions.index') }}" class="btn btn-outline-secondary btn-sm">
            Back to CPD course list
        </a>

        <p class="text-muted small mt-3 mb-0">
            You’ll be redirected to your online class automatically in a few seconds&hellip;
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-redirect to the player after 5 seconds
    setTimeout(function () {
        window.location.href = @json(route('cpd.learn.show', $session->course));
    }, 5000);
</script>
@endpush
