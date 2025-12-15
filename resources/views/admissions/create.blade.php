<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admission</title>
</head>
<body>
    <h1>Create Admission</h1>

    @if ($errors->any())
        <div style="color:red;">
            <strong>There were some problems with your input:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admissions.store') }}">
        @csrf

        <div>
            <label for="student_id">Student</label><br>
            <select name="student_id" id="student_id" required>
                <option value="">-- Select student --</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                        {{ $student->student_number }} - {{ $student->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label for="programme_id">Programme</label><br>
            <select name="programme_id" id="programme_id" required>
                <option value="">-- Select programme --</option>
                @foreach($programmes as $programme)
                    <option value="{{ $programme->id }}" {{ old('programme_id') == $programme->id ? 'selected' : '' }}>
                        {{ $programme->code }} - {{ $programme->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label for="level_of_entry">Level of Entry (year)</label><br>
            <input type="number" name="level_of_entry" id="level_of_entry"
                   value="{{ old('level_of_entry', 1) }}" min="1" max="10" required>
        </div>

        <br>

        <div>
            <label for="commencement_date">Commencement Date</label><br>
            <input type="date" name="commencement_date" id="commencement_date"
                   value="{{ old('commencement_date') }}" required>
        </div>

        <br>

        <div>
            <label for="expected_completion_date">Expected Completion Date</label><br>
            <input type="date" name="expected_completion_date" id="expected_completion_date"
                   value="{{ old('expected_completion_date') }}" required>
        </div>

        <br>

        <div>
            <label for="programme_cost">Programme Cost (Pula)</label><br>
            <input type="number" step="0.01" name="programme_cost" id="programme_cost"
                   value="{{ old('programme_cost') }}">
        </div>

        <br>

        <div>
            <label for="admission_status">Admission Status</label><br>
            <select name="admission_status" id="admission_status" required>
                @php $status = old('admission_status', 'Approved'); @endphp
                <option value="Pending"  {{ $status === 'Pending'  ? 'selected' : '' }}>Pending</option>
                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <br>

        <button type="submit">Save Admission</button>
        <a href="{{ route('admissions.index') }}">Back to Admissions</a>
    </form>
</body>
</html>
