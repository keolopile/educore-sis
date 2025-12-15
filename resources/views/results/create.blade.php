@extends('layouts.app')

@section('title', 'Capture Results – Pedagre SIS')

@section('content')
    <h1>Capture Results</h1>

    {{-- Registration summary --}}
    <div style="margin-bottom: 16px;">
        <strong>Registration:</strong><br>
        Student: {{ $registration->student->student_number }} – {{ $registration->student->full_name }}<br>
        Programme: {{ $registration->programme->code }} – {{ $registration->programme->name }}<br>
        Year/Semester: {{ $registration->study_year }}/{{ $registration->study_semester }}<br>
        Registration Date: {{ $registration->registration_date }}
    </div>

    @if ($errors->any())
        <div class="flash-error">
            <strong>There were some problems with your input:</strong>
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('results.store') }}">
        @csrf

        <input type="hidden" name="registration_id" value="{{ $registration->id }}">

        <div>
            <label>Academic Year</label><br>
            <input type="text" name="academic_year"
                   value="{{ old('academic_year', '2025/2026') }}">
        </div>

        <br>

        <div>
            <label>Exam Session</label><br>
            @php $session = old('exam_session', 'MAIN'); @endphp
            <select name="exam_session" required>
                <option value="MAIN"   {{ $session === 'MAIN'   ? 'selected' : '' }}>MAIN</option>
                <option value="SUPP"   {{ $session === 'SUPP'   ? 'selected' : '' }}>SUPP</option>
                <option value="REMARK" {{ $session === 'REMARK' ? 'selected' : '' }}>REMARK</option>
            </select>
        </div>

        <br>

        <div>
            <label>Overall Status</label><br>
            @php $status = old('overall_status', 'Pass'); @endphp
            <select name="overall_status" required>
                <option value="Pending"    {{ $status === 'Pending'    ? 'selected' : '' }}>Pending</option>
                <option value="Pass"       {{ $status === 'Pass'       ? 'selected' : '' }}>Pass</option>
                <option value="Fail"       {{ $status === 'Fail'       ? 'selected' : '' }}>Fail</option>
                <option value="Incomplete" {{ $status === 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
            </select>
        </div>

        <br>

        <div>
            <label>GPA</label><br>
            <input type="number" step="0.01" name="gpa"
                   value="{{ old('gpa') }}" min="0" max="4">
        </div>

        <br>

        <div>
            <label>Remarks</label><br>
            <textarea name="remarks" rows="3">{{ old('remarks') }}</textarea>
        </div>

        <br>

        <h3>Modules and Marks</h3>
        <p>These modules are loaded from the selected registration.</p>

        <table>
            <thead>
            <tr>
                <th>Module Code</th>
                <th>Module Name</th>
                <th>Mark</th>
                <th>Grade</th>
            </tr>
            </thead>
            <tbody>
            @forelse($modules as $module)
                <tr>
                    <td>{{ $module->code }}</td>
                    <td>{{ $module->name }}</td>
                    <td>
                        <input type="number"
                               name="marks[{{ $module->id }}]"
                               step="0.01"
                               min="0"
                               max="100"
                               style="width:80px;"
                               value="{{ old('marks.'.$module->id) }}">
                    </td>
                    <td>
                        <input type="text"
                               name="grades[{{ $module->id }}]"
                               style="width:60px;"
                               value="{{ old('grades.'.$module->id) }}">
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">This registration has no modules attached.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <br>

        <button type="submit" class="btn btn-primary">Save Result</button>
        <a href="{{ route('results.index') }}" class="btn">Back to Results</a>
    </form>
@endsection
