@extends('layouts.app')

@section('title', 'Import DTEF Results')

@section('content')
    <h1>Import DTEF Results</h1>

    @if ($errors->any())
        <div class="flash-error">
            {{ implode(', ', $errors->all()) }}
        </div>
    @endif

    <p>
        This form expects the IDM results sheet with columns like:
        <em>INSTITUTION, SURNAME, FIRST NAME, OMANG, PROGRAMME CODE, YEAR OF STUDY, SEMESTER,
        COMPLETION DATE, DATE OF REGISTRATION, NO OF MODULES, MODELS ENROLLED FOR, TERM ENDING,
        MODELS PASSED, RETAKE MODULES, SUPPLEMENTARY/RESIT MODULES, ACADEMIC OUTCOME</em>.
    </p>

    <form method="POST" action="{{ route('dtef.results_import.handle') }}" enctype="multipart/form-data">
        @csrf

        <p>
            <label for="file">Results file (Excel):</label><br>
            <input type="file" name="file" id="file" required>
        </p>

        <button type="submit" class="btn btn-primary">
            Upload &amp; Import Results
        </button>
    </form>
@endsection
