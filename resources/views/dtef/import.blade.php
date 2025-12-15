@extends('layouts.app')

@section('title', 'Import DTEF Register – Pedagre SIS')

@section('content')
    <h1>Import DTEF Register</h1>

    <p>
        Upload the IDM / institution DTEF register in the standard format.
        The system will create or update Students, Programmes, Admissions and Registrations.
    </p>

    {{-- Template download --}}
    <p style="margin-bottom: 16px;">
        <strong>Step 1:</strong> Download the Excel template, fill it, then upload it below.<br>
        {{-- In production, copy the template into public/ and change this href to asset('dtef_register_template.xlsx') --}}
        <a href="{{ asset('dtef_register_template.xlsx') }}" class="btn btn-primary">
            Download DTEF Register Template
        </a>
    </p>

    @if ($errors->any())
        <div class="flash-error">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dtef.import.handle') }}" enctype="multipart/form-data">
        @csrf

        <div>
            <label for="file"><strong>Step 2:</strong> Choose completed DTEF register (.xlsx)</label><br>
            <input type="file" id="file" name="file" required>
        </div>

        <br>

        <button type="submit" class="btn btn-primary">Upload & Import</button>
        <a href="{{ route('registrations.index') }}" class="btn">Back to Registrations</a>
    </form>
@endsection
