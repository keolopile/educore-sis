@extends('layouts.app')

@section('title', 'Students')

@section('content')
    <style>
        .students-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .students-header h1 {
            margin: 0;
        }

        .table-sm th,
        .table-sm td {
            padding: 4px 6px;
            font-size: 13px;
        }

        .pill-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            line-height: 1.2;
            white-space: nowrap;
        }
        .pill-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .pill-inactive {
            background-color: #e5e7eb;
            color: #374151;
        }
        .pill-suspended {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .muted {
            color: #6b7280;
            font-size: 12px;
        }
    </style>

    <div class="students-header">
        <h1>Students</h1>
        {{-- Later we can add "Add student" or filters here --}}
    </div>

    <table class="table-sm" border="1" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="width:40px;">ID</th>
            <th style="width:80px;">Institution</th>
            <th style="width:120px;">Student No</th>
            <th>Full Name</th>
            <th style="width:130px;">National ID</th>
            <th style="width:80px;">Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($students as $student)
            @php
                $instCode = $student->institution->short_code ?? '';

                // Student number fallback: if missing, show national_id instead
                $studNo = trim($student->student_number ?? '');
                $missingStudNo = false;

                if ($studNo === '') {
                    $studNo = $student->national_id ?: '—';
                    $missingStudNo = true;
                }

                $rawStatus   = trim($student->status ?? 'Active');
                $statusUpper = strtoupper($rawStatus);
                $statusClass = 'pill-inactive';

                if ($statusUpper === 'ACTIVE') {
                    $statusClass = 'pill-active';
                } elseif (in_array($statusUpper, ['SUSPENDED', 'EXPELLED'])) {
                    $statusClass = 'pill-suspended';
                }
            @endphp

            <tr>
                <td>{{ $student->id }}</td>
                <td>{{ $instCode }}</td>
                <td>
                    @if($missingStudNo)
                        <span class="muted" title="Student number not set; showing National ID">
                            {{ $studNo }}
                        </span>
                    @else
                        {{ $studNo }}
                    @endif
                </td>
                <td>{{ $student->full_name ?? trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name) }}</td>
                <td>{{ $student->national_id }}</td>
                <td>
                    <span class="pill-status {{ $statusClass }}">
                        {{ $rawStatus ?: 'Active' }}
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6">No students yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
