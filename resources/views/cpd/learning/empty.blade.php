{{-- resources/views/cpd/learning/empty.blade.php --}}
@extends('layouts.cpd')

@section('title', $course->title . ' – Online content coming soon')

@section('content')
<div class="page-wrap">
    <div class="container" style="max-width: 960px;">

        <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">
            {{ $course->title }}
        </h1>

        <p class="small-muted" style="margin-bottom: 18px;">
            IDM Centre for Continuing Professional Development
        </p>

        <div class="alert alert-info" style="font-size: 14px;">
            <strong>Online content not yet available.</strong><br>
            The video lessons and learning path for this course are still being
            prepared in the CPD platform. Once the modules and lessons have been
            added, you’ll be able to access them here.
        </div>

        <p style="font-size: 14px; color:#4b5563; margin-bottom: 20px;">
            If you believe this is an error, please contact IDM CCPD and quote the
            course name: <strong>{{ $course->title }}</strong>.
        </p>

        <a href="{{ route('cpd.sessions.index') }}" class="btn btn-secondary btn-sm">
            ← Back to CPD course list
        </a>
    </div>
</div>
@endsection
