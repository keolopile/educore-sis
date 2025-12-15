<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Registration</title>
</head>
<body>
    <h1>Create Registration</h1>

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('registrations.store') }}">
        @csrf

        <div>
            <label>Student</label><br>
            <select name="student_id" required>
                <option value="">-- Select student --</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->student_number }} - {{ $s->full_name }}</option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Programme</label><br>
            <select name="programme_id" required>
                <option value="">-- Select programme --</option>
                @foreach($programmes as $p)
                    <option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <br>

        <div>
            <label>Study Year</label><br>
            <input type="number" name="study_year" value="{{ old('study_year', 1) }}" min="1" max="10" required>
        </div>

        <br>

        <div>
            <label>Study Semester</label><br>
            <input type="number" name="study_semester" value="{{ old('study_semester', 1) }}" min="1" max="4" required>
        </div>

        <br>

        <div>
            <label>Registration Date</label><br>
            <input type="date" name="registration_date" value="{{ old('registration_date') }}" required>
        </div>

        <br>

        <div>
            <label>
                <input type="checkbox" name="accommodation" value="1" {{ old('accommodation') ? 'checked' : '' }}>
                Needs accommodation (hostel)
            </label>
        </div>

        <br>

        <div>
            <label>Modules</label><br>
            @foreach($modules as $m)
                <label>
                    <input type="checkbox" name="module_ids[]" value="{{ $m->id }}">
                    {{ $m->code }} - {{ $m->name }}
                </label><br>
            @endforeach
        </div>

        <br>

        <button type="submit">Save Registration</button>
        <a href="{{ route('registrations.index') }}">Back to Registrations</a>
    </form>
</body>
</html>
