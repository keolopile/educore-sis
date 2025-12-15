@extends('layouts.cpd_admin')

@section('title', 'CPD Admin – Dashboard')

@section('content')
    <h1 style="font-size:20px; margin-bottom:6px;">CPD Admin dashboard</h1>
    <p class="text-muted" style="font-size:13px; margin-bottom:18px;">
        Quick overview of your CPD catalogue, intakes and learner activity.
    </p>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted" style="font-size:11px;">CPD courses</div>
                    <div style="font-size:24px; font-weight:600;">{{ $coursesCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted" style="font-size:11px;">Sessions / intakes</div>
                    <div style="font-size:24px; font-weight:600;">{{ $sessionsCount }}</div>
                    <div style="font-size:11px; color:#16a34a;">
                        {{ $upcomingCount }} upcoming
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted" style="font-size:11px;">Total enrolments</div>
                    <div style="font-size:24px; font-weight:600;">{{ $enrolments }}</div>
                    <div style="font-size:11px; color:#4b5563;">
                        {{ $paidEnrolments }} paid / active
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted" style="font-size:11px;">Pending payments</div>
                    <div style="font-size:24px; font-weight:600;">{{ $pendingPayments }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h2 style="font-size:16px; margin-bottom:8px;">Quick links</h2>
        <a href="{{ route('admin.cpd.courses.index') }}" class="btn btn-sm btn-primary">
            Manage CPD courses
        </a>
        <a href="{{ route('cpd.sessions.index') }}" class="btn btn-sm btn-outline-secondary ms-1">
            View public catalogue
        </a>
    </div>
@endsection
