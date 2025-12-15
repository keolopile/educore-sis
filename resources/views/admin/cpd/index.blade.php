{{-- resources/views/admin/cpd/enrolments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'CPD Enrolments Dashboard')

@section('content')
<div class="container-fluid py-3">

    {{-- Top title + stats --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1">CPD Enrolments</h1>
            <p class="text-muted mb-0">
                Overview of all registrations across CPD sessions – filter by status and search by learner or course.
            </p>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
            <div class="card border-success h-100">
                <div class="card-body py-2">
                    <small class="text-muted text-uppercase">Total enrolments</small>
                    <div class="h5 mb-0">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-primary h-100">
                <div class="card-body py-2">
                    <small class="text-muted text-uppercase">Active</small>
                    <div class="h5 mb-0">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card border-warning h-100">
                <div class="card-body py-2">
                    <small class="text-muted text-uppercase">Pending</small>
                    <div class="h5 mb-0">{{ $stats['pending'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-success h-100">
                <div class="card-body py-2">
                    <small class="text-muted text-uppercase">Paid</small>
                    <div class="h5 mb-0">{{ $stats['paid'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-danger h-100">
                <div class="card-body py-2">
                    <small class="text-muted text-uppercase">Unpaid / failed</small>
                    <div class="h5 mb-0">{{ $stats['unpaid'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label form-label-sm">Search</label>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           class="form-control form-control-sm"
                           placeholder="Learner name, email, course title, code">
                </div>

                <div class="col-md-3">
                    <label class="form-label form-label-sm">Enrolment status</label>
                    <select name="enrolment_status" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach(['pending','active','cancelled','completed'] as $status)
                            <option value="{{ $status }}"
                                @selected(request('enrolment_status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label form-label-sm">Payment status</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach(['pending','paid','failed','refunded'] as $p)
                            <option value="{{ $p }}"
                                @selected(request('payment_status') === $p)>
                                {{ ucfirst($p) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 text-end">
                    <button class="btn btn-sm btn-primary w-100">
                        Apply filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Enrolments table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Learner</th>
                        <th>Course / Session</th>
                        <th>Dates</th>
                        <th>Sponsorship</th>
                        <th>Enrolment</th>
                        <th>Payment</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($enrolments as $enrolment)
                        @php
                            $session = $enrolment->session;
                            $course  = $session->course ?? null;
                        @endphp
                        <tr>
                            <td>
                                <div><strong>{{ $enrolment->user->name }}</strong></div>
                                <div class="text-muted small">
                                    {{ $enrolment->user->email }}<br>
                                    @if($enrolment->id_number)
                                        ID: {{ $enrolment->id_number }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>
                                    {{ $course?->title ?? 'CPD Course' }}
                                </div>
                                <div class="text-muted small">
                                    Session #{{ $session->id }}
                                    @if($session->location)
                                        • {{ $session->location }}
                                    @endif
                                </div>
                            </td>
                            <td class="small">
                                {{ optional($session->start_date)->format('d M Y') ?? 'TBA' }}
                                @if($session->end_date && $session->end_date != $session->start_date)
                                    – {{ optional($session->end_date)->format('d M Y') }}
                                @endif
                            </td>
                            <td class="small">
                                {{ $enrolment->sponsorship_type
                                    ? ucfirst($enrolment->sponsorship_type)
                                    : 'Not set' }}
                            </td>
                            <td>
                                @php
                                    $badge = match($enrolment->enrolment_status) {
                                        'active'    => 'success',
                                        'pending'   => 'warning',
                                        'completed' => 'primary',
                                        'cancelled' => 'secondary',
                                        default     => 'light',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">
                                    {{ ucfirst($enrolment->enrolment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $pBadge = match($enrolment->payment_status) {
                                        'paid'    => 'success',
                                        'failed'  => 'danger',
                                        'refunded'=> 'secondary',
                                        'pending' => 'warning',
                                        default   => 'light',
                                    };
                                @endphp
                                <span class="badge bg-{{ $pBadge }}">
                                    {{ ucfirst($enrolment->payment_status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.cpd.enrolments.show', $enrolment) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View learner
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                No enrolments found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($enrolments->hasPages())
            <div class="card-footer py-2">
                {{ $enrolments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
