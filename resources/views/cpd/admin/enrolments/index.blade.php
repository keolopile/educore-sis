@extends('layouts.cpd')

@section('title', 'CPD enrolments')

@section('head')
<style>
    .cpd-admin-shell {
        padding: 20px 10px 30px;
    }

    .cpd-admin-header {
        margin-bottom: 14px;
    }
    .cpd-admin-header h1 {
        font-size: 18px;
        margin: 0 0 4px;
        font-weight: 600;
    }
    .cpd-admin-subtitle {
        font-size: 12px;
        color: #6b7280;
    }

    .cpd-admin-stat-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }
    .cpd-stat-card {
        background: #f9fafb;
        border-radius: 8px;
        padding: 8px 10px;
        border: 1px solid #e5e7eb;
    }
    .cpd-stat-label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .cpd-stat-value {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .cpd-admin-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: flex-end;
        margin-bottom: 10px;
    }
    .cpd-admin-filters .form-label {
        font-size: 11px;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
    }
    .cpd-admin-filters .form-control,
    .cpd-admin-filters .form-select {
        font-size: 12px;
        padding: 4px 8px;
        height: 30px;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
    }
    .badge-status-active {
        background: #dcfce7;
        color: #166534;
    }
    .badge-status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-payment-paid {
        background: #e0f2fe;
        color: #075985;
    }
    .badge-payment-pending {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-payment-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .cpd-admin-table-wrap {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        overflow: hidden;
    }
    .cpd-admin-table-wrap table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .cpd-admin-table-wrap th,
    .cpd-admin-table-wrap td {
        padding: 6px 8px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .cpd-admin-table-wrap th {
        background: #f9fafb;
        font-weight: 600;
        color: #4b5563;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .cpd-admin-table-wrap tbody tr:nth-child(even) {
        background: #f9fafb;
    }
    .cpd-admin-table-wrap tbody tr:hover {
        background: #ecfdf3;
    }

    .cpd-learner-name {
        font-weight: 600;
        font-size: 12px;
        color: #111827;
    }
    .cpd-learner-email {
        font-size: 11px;
        color: #4b5563;
    }
    .cpd-course-title {
        font-weight: 600;
        font-size: 12px;
    }
    .cpd-session-meta {
        font-size: 11px;
        color: #4b5563;
    }

    .cpd-money {
        font-weight: 600;
        font-size: 12px;
    }
    .cpd-money span {
        font-weight: 400;
        font-size: 11px;
        color: #6b7280;
    }

    /* 🔽 Fix Laravel Tailwind pagination arrows being huge */
    nav[aria-label="Pagination Navigation"] {
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }
    nav[aria-label="Pagination Navigation"] svg {
        width: 16px;
        height: 16px;
    }
    nav[aria-label="Pagination Navigation"] span,
    nav[aria-label="Pagination Navigation"] a {
        font-size: 12px;
    }

    @media (max-width: 992px) {
        .cpd-admin-stat-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .cpd-admin-stat-grid {
            grid-template-columns: 1fr;
        }
        .cpd-admin-filters {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>
@endsection

@section('content')
<div class="cpd-admin-shell">

    <div class="cpd-admin-header">
        <h1>CPD enrolments</h1>
        <p class="cpd-admin-subtitle">
            Overview of registrations for all CPD sessions. Use the filters to track who has registered, paid, and been activated.
        </p>
    </div>

    {{-- Stats row --}}
    <div class="cpd-admin-stat-grid">
        <div class="cpd-stat-card">
            <div class="cpd-stat-label">Total enrolments</div>
            <div class="cpd-stat-value">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div class="cpd-stat-card">
            <div class="cpd-stat-label">Active learners</div>
            <div class="cpd-stat-value">{{ $stats['active'] ?? 0 }}</div>
        </div>
        <div class="cpd-stat-card">
            <div class="cpd-stat-label">Pending enrolments</div>
            <div class="cpd-stat-value">{{ $stats['pending'] ?? 0 }}</div>
        </div>
        <div class="cpd-stat-card">
            <div class="cpd-stat-label">Payments marked paid</div>
            <div class="cpd-stat-value">{{ $stats['paid'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="cpd-admin-filters">
        <div>
            <label class="form-label" for="q">Search</label>
            <input type="text"
                   name="q"
                   id="q"
                   class="form-control"
                   placeholder="Learner name, email, or course"
                   value="{{ request('q') }}">
        </div>

        <div>
            <label class="form-label" for="enrolment_status">Enrolment status</label>
            <select name="status" id="enrolment_status" class="form-select">
                <option value="">Any</option>
                <option value="active"   @selected(request('status') === 'active')>Active</option>
                <option value="pending"  @selected(request('status') === 'pending')>Pending</option>
                <option value="cancelled"@selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </div>

        <div>
            <label class="form-label" for="payment_status">Payment status</label>
            <select name="payment_status" id="payment_status" class="form-select">
                <option value="">Any</option>
                <option value="paid"    @selected(request('payment_status') === 'paid')>Paid</option>
                <option value="pending" @selected(request('payment_status') === 'pending')>Pending</option>
                <option value="failed"  @selected(request('payment_status') === 'failed')>Failed</option>
            </select>
        </div>

        <div>
            <button class="btn btn-primary" style="margin-top: 18px;">Apply filters</button>
        </div>
    </form>

    {{-- Table --}}
    <div class="cpd-admin-table-wrap">
        <table>
            <thead>
            <tr>
                <th>Learner</th>
                <th>Course / Session</th>
                <th>Dates &amp; mode</th>
                <th>Enrolment</th>
                <th>Payment</th>
                <th>Registered at</th>
            </tr>
            </thead>
            <tbody>
            @forelse($enrolments as $enrolment)
                @php
                    $session = $enrolment->session;
                    $course  = $session?->course;
                    $domain  = $course?->domain?->name;
                    $latestPayment = $enrolment->payments->first();
                @endphp
                <tr>
                    <td>
                        <div class="cpd-learner-name">
                            {{ $enrolment->user?->name ?? '—' }}
                        </div>
                        <div class="cpd-learner-email">
                            {{ $enrolment->user?->email ?? '—' }}
                        </div>
                        @if($enrolment->organisation_name)
                            <div class="cpd-session-meta">
                                {{ $enrolment->organisation_name }}
                                @if($enrolment->position_title)
                                    – {{ $enrolment->position_title }}
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="cpd-course-title">
                            {{ $course?->title ?? '—' }}
                        </div>
                        <div class="cpd-session-meta">
                            @if($domain)
                                {{ $domain }} ·
                            @endif
                            Intake #{{ $session?->id }}
                        </div>
                    </td>
                    <td>
                        <div class="cpd-session-meta">
                            {{ optional($session?->start_date)->format('d M Y') }}
                            @if($session?->end_date && $session->end_date != $session->start_date)
                                – {{ optional($session->end_date)->format('d M Y') }}
                            @endif
                        </div>
                        <div class="cpd-session-meta">
                            {{ ucfirst($session?->delivery_mode ?? 'mode TBA') }}
                            @if($session?->location)
                                · {{ $session->location }}
                            @endif
                        </div>
                    </td>
                    <td>
                        @php
                            $enrolStatus = $enrolment->enrolment_status ?? 'pending';
                        @endphp
                        <span class="badge-status
                            {{ $enrolStatus === 'active' ? 'badge-status-active' : '' }}
                            {{ $enrolStatus === 'pending' ? 'badge-status-pending' : '' }}
                            {{ $enrolStatus === 'cancelled' ? 'badge-status-cancelled' : '' }}
                        ">
                            {{ ucfirst($enrolStatus) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $payStatus = $enrolment->payment_status ?? 'pending';
                        @endphp
                        <div>
                            <span class="badge-status
                                {{ $payStatus === 'paid' ? 'badge-payment-paid' : '' }}
                                {{ $payStatus === 'pending' ? 'badge-payment-pending' : '' }}
                                {{ $payStatus === 'failed' ? 'badge-payment-failed' : '' }}
                            ">
                                {{ ucfirst($payStatus) }}
                            </span>
                        </div>
                        @if($latestPayment)
                            <div class="cpd-money">
                                {{ $latestPayment->currency ?? 'BWP' }}
                                {{ number_format($latestPayment->amount, 2) }}
                                <span>
                                    ({{ $latestPayment->method ?? '—' }})
                                </span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="cpd-session-meta">
                            {{ optional($enrolment->created_at)->format('d M Y H:i') }}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 12px; text-align: center; color:#6b7280;">
                        No enrolments found. Try adjusting your filters.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($enrolments->hasPages())
        {{ $enrolments->links() }}
    @endif

</div>
@endsection
