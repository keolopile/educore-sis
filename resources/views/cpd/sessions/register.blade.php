{{-- resources/views/cpd/sessions/register.blade.php --}}
@extends('layouts.cpd')

@section('title', 'Register – ' . $session->course->title)

@section('content')
<div class="page-wrap">
    <div class="container" style="max-width: 1100px;">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="small-muted mb-1">
                    {{ $session->course->domain->name ?? 'CPD Programme' }}
                </div>
                <h1 style="font-size:22px; margin-bottom:4px;">
                    Register for {{ $session->course->title }}
                </h1>
                <div class="small-muted">
                    {{ optional($session->start_date)->format('d M Y') }}
                    @if($session->end_date && $session->end_date !== $session->start_date)
                        – {{ optional($session->end_date)->format('d M Y') }}
                    @endif
                    · {{ ucfirst(str_replace('_',' ', $session->delivery_mode)) }}
                    @if($session->location)
                        · {{ $session->location }}
                    @endif
                </div>
            </div>
            <div class="text-end">
                @php
                    $price = $session->price ?? $session->course->default_price;
                    $curr  = $session->currency ?? $session->course->currency ?? 'BWP';
                @endphp
                @if($price)
                    <div style="font-size:18px; font-weight:700; color:#047857;">
                        {{ $curr }} {{ number_format($price, 2) }}
                    </div>
                    <div class="small-muted">
                        per participant
                    </div>
                @endif
            </div>
        </div>

        @if(session('status'))
            <div class="flash-success">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="flash-error">
                <strong>Kindly check the form:</strong>
                <ul style="margin:4px 0 0 16px; padding:0;">
                    @foreach($errors->all() as $error)
                        <li style="font-size:12px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row" style="gap:18px;">
            {{-- Left: Registration form --}}
            <div class="col-md-7" style="flex: 0 0 58%; max-width:58%;">
                <form method="post" action="{{ route('cpd.sessions.register.store', $session) }}">
                    @csrf

                    {{-- Section A: Personal details --}}
                    <h2 style="font-size:16px; margin-bottom:8px;">A. Personal details</h2>
                    <div class="small-muted mb-2">
                        These details will appear on your certificate. Please ensure correct spelling.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small">Full name *</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">ID / Passport No.</label>
                            <input type="text"
                                   name="id_number"
                                   value="{{ old('id_number') }}"
                                   class="form-control form-control-sm @error('id_number') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Email *</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                                   required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Mobile number</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   class="form-control form-control-sm @error('phone') is-invalid @enderror">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Gender</label>
                            <select name="gender"
                                    class="form-select form-select-sm @error('gender') is-invalid @enderror">
                                <option value="">Select</option>
                                <option value="male"   @selected(old('gender') === 'male')>Male</option>
                                <option value="female" @selected(old('gender') === 'female')>Female</option>
                                <option value="other"  @selected(old('gender') === 'other')>Other / Prefer not to say</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Postal address</label>
                        <textarea name="address"
                                  rows="2"
                                  class="form-control form-control-sm @error('address') is-invalid @enderror"
                                  placeholder="P.O. Box..., Gaborone">
                            {{ old('address') }}
                        </textarea>
                    </div>

                    {{-- Section B: Employment details --}}
                    <h2 style="font-size:16px; margin:16px 0 8px;">B. Employment details</h2>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Employer / Organisation</label>
                            <input type="text"
                                   name="employer"
                                   value="{{ old('employer', old('organisation_name')) }}"
                                   class="form-control form-control-sm @error('employer') is-invalid @enderror">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Designation / Position</label>
                            <input type="text"
                                   name="designation"
                                   value="{{ old('designation', old('position_title')) }}"
                                   class="form-control form-control-sm @error('designation') is-invalid @enderror">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Department / Unit</label>
                            <input type="text"
                                   name="department"
                                   value="{{ old('department') }}"
                                   class="form-control form-control-sm @error('department') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small">Work phone</label>
                            <input type="text"
                                   name="work_phone"
                                   value="{{ old('work_phone') }}"
                                   class="form-control form-control-sm @error('work_phone') is-invalid @enderror">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small">Work email</label>
                            <input type="email"
                                   name="work_email"
                                   value="{{ old('work_email') }}"
                                   class="form-control form-control-sm @error('work_email') is-invalid @enderror">
                        </div>
                    </div>

                    {{-- Section C: Sponsorship --}}
                    <h2 style="font-size:16px; margin:16px 0 8px;">C. Sponsorship</h2>
                    <div class="mb-3">
                        <label class="form-label small d-block">Who will pay for this course?</label>
                        <div class="d-flex flex-wrap gap-3 small">
                            <label class="d-flex align-items-center" style="gap:6px;">
                                <input type="radio"
                                       name="sponsorship_type"
                                       value="self"
                                       {{ old('sponsorship_type') === 'self' ? 'checked' : '' }}>
                                <span>Self-sponsored</span>
                            </label>
                            <label class="d-flex align-items-center" style="gap:6px;">
                                <input type="radio"
                                       name="sponsorship_type"
                                       value="employer"
                                       {{ old('sponsorship_type') === 'employer' ? 'checked' : '' }}>
                                <span>Sponsored by employer</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <a href="{{ route('cpd.sessions.index') }}" class="btn btn-secondary btn-sm">
                            ← Back to course list
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Continue to payment
                        </button>
                    </div>
                </form>
            </div>

            {{-- Right: Session summary card --}}
            <div class="col-md-4" style="flex: 0 0 38%; max-width:38%;">
                <div style="
                    border:1px solid #d1d5db;
                    border-radius:10px;
                    padding:14px;
                    background:#f9fafb;
                ">
                    <div style="font-weight:600; margin-bottom:6px;">
                        {{ $session->course->title }}
                    </div>
                    <div class="small-muted mb-2">
                        {{ $session->course->code ?? '' }}
                    </div>

                    <div style="font-size:13px; margin-bottom:8px;">
                        <strong>Dates:</strong>
                        {{ optional($session->start_date)->format('d M Y') }}
                        @if($session->end_date && $session->end_date !== $session->start_date)
                            – {{ optional($session->end_date)->format('d M Y') }}
                        @endif
                        <br>
                        <strong>Mode:</strong>
                        {{ ucfirst(str_replace('_',' ', $session->delivery_mode)) }}
                        @if($session->location)
                            <br>
                            <strong>Location:</strong> {{ $session->location }}
                        @endif
                    </div>

                    @if($price)
                        <div style="border-top:1px dashed #e5e7eb; margin:8px 0; padding-top:8px;">
                            <div style="font-size:18px; font-weight:700; color:#047857;">
                                {{ $curr }} {{ number_format($price, 2) }}
                            </div>
                            <div class="small-muted">
                                Inclusive of tuition and materials. Travel and accommodation not included.
                            </div>
                        </div>
                    @endif

                    <div class="small-muted" style="margin-top:6px;">
                        By proceeding you confirm that the details provided are correct and that you
                        agree to IDM’s registration and cancellation policy.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
