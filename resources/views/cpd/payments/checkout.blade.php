@extends('layouts.cpd')

@section('title', 'CPD – Checkout')

@section('content')
<div class="container py-4">
    <h1 class="mb-3">Confirm & Pay</h1>

    @if(session('status'))
        <div class="flash-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-3 small-muted">
        Step 2 of 2 – payment
    </div>

    <div class="mb-4">
        <h2 style="font-size: 16px; margin-bottom: 6px;">
            {{ $payment->enrolment->session->course->title }}
        </h2>
        <div class="small-muted">
            {{ $payment->enrolment->session->start_date->format('d M Y') }}
            @if($payment->enrolment->session->end_date)
                – {{ $payment->enrolment->session->end_date->format('d M Y') }}
            @endif
            · {{ strtoupper($payment->currency) }}
            {{ number_format($payment->amount, 2) }}
        </div>
        <div class="small-muted">
            Participant: {{ $payment->user->name }} ({{ $payment->user->email }})
        </div>
        @if($payment->enrolment->organisation_name)
            <div class="small-muted">
                Organisation: {{ $payment->enrolment->organisation_name }}
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('cpd.payments.start', $payment) }}">
        @csrf

        <div class="mb-3">
            <label class="small-muted" style="display:block; margin-bottom:4px;">
                Payment method
            </label>
            <select name="method" class="form-select" style="padding:6px 8px; font-size:13px;">
                <option value="card">Bank card</option>
                <option value="orange_money">Orange Money</option>
                <option value="eft">EFT / Bank transfer</option>
            </select>
            <div class="small-muted" style="margin-top:4px;">
                In <strong>mock</strong> mode this will redirect to an internal
                success page. Once connected to the real gateway, this will open
                the provider’s secure payment screen.
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            Pay Now
        </button>
        <a href="{{ route('cpd.sessions.index') }}" class="btn btn-secondary" style="margin-left:8px;">
            Cancel
        </a>
    </form>
</div>
@endsection
