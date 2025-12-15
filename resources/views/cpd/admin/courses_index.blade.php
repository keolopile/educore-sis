@extends('layouts.cpd_admin')

@section('title', 'CPD Admin – Courses')

@section('content')
    <h1 style="font-size:20px; margin-bottom:6px;">CPD courses</h1>
    <p class="text-muted" style="font-size:13px; margin-bottom:16px;">
        Manage your CPD course catalogue – open the curriculum builder, review sessions and enrolments.
    </p>

    <div class="table-responsive">
        <table class="table table-sm align-middle" style="font-size:13px;">
            <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th>Course</th>
                <th style="width:140px;">Domain</th>
                <th style="width:80px;">Modules</th>
                <th style="width:90px;">Lessons</th>
                <th style="width:160px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($courses as $i => $course)
                @php
                    $modulesCount = $course->modules->count();
                    $lessonsCount = $course->modules->flatMap->lessons->count();
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $course->title }}</div>
                        @if($course->code)
                            <div style="font-size:11px; color:#6b7280;">{{ $course->code }}</div>
                        @endif
                    </td>
                    <td>{{ $course->domain->name ?? '—' }}</td>
                    <td>{{ $modulesCount }}</td>
                    <td>{{ $lessonsCount }}</td>
                    <td>
                        <a href="{{ route('admin.cpd.courses.curriculum.edit', $course) }}"
                           class="btn btn-sm btn-outline-primary mb-1">
                            Curriculum
                        </a>
                        {{-- later we can add "Sessions" and "Enrolments" admin pages --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-muted">
                        No CPD courses found yet. Once you create courses, they will appear here.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
