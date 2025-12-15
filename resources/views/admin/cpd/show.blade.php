{{-- resources/views/admin/cpd/enrolments/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Learner – ' . $enrolment->user->name)

@section('content')
<div class="container-fluid py-3">
    <div class="mb-3">
        <a href="{{ route('admin.cpd.enrolments.index') }}" class="btn btn-sm btn-outline-secondary">
            ← Back to enrolments
        </a>
    </div>

    <div class="row g-3">
        {{-- Left: learner & enrolment info --}}
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header">
                    Learner profile
                </div>
                <div class="card-body">
                    <h5 class="mb-1">{{ $enrolment->user->name }}</h5>
                    <div class="text-muted mb-2">{{ $enrolment->user->email }}</div>

                    <dl class="row mb-0 small">
                        @if($enrolment->id_number)
                            <dt class="col-5">ID number</dt>
                            <dd class="col-7">{{ $enrolment->id_number }}</dd>
                        @endif

                        @if($enrolment->phone)
                            <dt class="col-5">Phone</dt>
                            <dd class="col-7">{{ $enrolment->phone }}</dd>
                        @endif

                        @if($enrolment->address)
                            <dt class="col-5">Address</dt>
                            <dd class="col-7">{{ $enrolment->address }}</dd>
                        @endif

                        @if($enrolment->employer)
                            <dt class="col-5">Employer</dt>
                            <dd class="col-7">{{ $enrolment->employer }}</dd>
                        @endif

                        @if($enrolment->designation)
                            <dt class="col-5">Designation</dt>
                            <dd class="col-7">{{ $enrolment->designation }}</dd>
                        @endif

                        @if($enrolment->department)
                            <dt class="col-5">Department</dt>
                            <dd class="col-7">{{ $enrolment->department }}</dd>
                        @endif

                        @if($enrolment->work_phone)
                            <dt class="col-5">Work phone</dt>
                            <dd class="col-7">{{ $enrolment->work_phone }}</dd>
                        @endif

                        @if($enrolment->work_email)
                            <dt class="col-5">Work email</dt>
                            <dd class="col-7">{{ $enrolment->work_email }}</dd>
                        @endif

                        @if($enrolment->sponsorship_type)
                            <dt class="col-5">Sponsorship</dt>
                            <dd class="col-7">{{ ucfirst($enrolment->sponsorship_type) }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Enrolment & payment
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-info">{{ ucfirst($enrolment->enrolment_status) }}</span>
                    </p>
                    <p class="mb-2">
                        <strong>Payment:</strong>
                        <span class="badge bg-{{ $enrolment->payment_status === 'paid' ? 'success' : 'warning' }}">
                            {{ ucfirst($enrolment->payment_status) }}
                        </span>
                    </p>

                    @if($enrolment->payments->isNotEmpty())
                        <p class="mb-1"><strong>Payments:</strong></p>
                        <ul class="list-unstyled mb-0">
                            @foreach($enrolment->payments as $payment)
                                <li class="mb-1">
                                    {{ $payment->currency }} {{ number_format($payment->amount, 2) }}
                                    – {{ ucfirst($payment->status) }}
                                    <span class="text-muted">
                                        ({{ $payment->created_at->format('d M Y H:i') }})
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No payments recorded.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: course + progress --}}
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Course & session</span>
                    <a href="{{ route('cpd.sessions.show', $enrolment->session) }}"
                       class="btn btn-sm btn-outline-secondary">
                        View public session page
                    </a>
                </div>
                <div class="card-body small">
                    <h5 class="mb-1">{{ $course->title }}</h5>
                    <div class="text-muted mb-2">
                        {{ optional($enrolment->session->start_date)->format('d M Y') ?? 'TBA' }}
                        @if($enrolment->session->end_date && $enrolment->session->end_date != $enrolment->session->start_date)
                            – {{ optional($enrolment->session->end_date)->format('d M Y') }}
                        @endif
                        @if($enrolment->session->location)
                            • {{ $enrolment->session->location }}
                        @endif
                    </div>

                    @if($course->summary)
                        <p class="mb-0">{{ $course->summary }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Learning progress
                </div>
                <div class="card-body">
                    @if($totalLessons === 0)
                        <p class="text-muted mb-0">
                            This course has no lessons defined in the curriculum yet.
                        </p>
                    @else
                        <div class="mb-2 d-flex justify-content-between small">
                            <span>
                                Completed {{ $completedLessons }} of {{ $totalLessons }} lessons
                            </span>
                            <span>
                                {{ $progressPercent }}%
                            </span>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: {{ $progressPercent }}%;"
                                 aria-valuenow="{{ $progressPercent }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>

                        <p class="text-muted small mb-0">
                            Progress is calculated based on completed lessons for this learner in the
                            course’s video curriculum.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
