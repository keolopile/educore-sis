@extends('layouts.app')

@section('title', 'Registrations – Pedagre SIS')

@section('content')
    <style>
        .registrations-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .registrations-header h1 {
            margin: 0;
        }

        .table-sm th,
        .table-sm td {
            padding: 4px 6px;
            font-size: 13px;
        }

        .pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            line-height: 1.2;
            white-space: nowrap;
        }
        .pill-approved {
            background-color: #dcfce7;
            color: #166534;
        }
        .pill-pending {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .pill-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .pill-other {
            background-color: #e5e7eb;
            color: #374151;
        }

        .pill-dtef-sent {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        .pill-dtef-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .pill-dtef-imported {
            background-color: #f5f3ff;
            color: #4c1d95;
        }

        .dtef-response-wrapper {
            margin-top: 4px;
        }
        .dtef-response-wrapper textarea {
            width: 100%;
            font-size: 11px;
            font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
        }
        .dtef-response-wrapper small {
            font-size: 11px;
            color: #6b7280;
        }

        .btn-xs {
            padding: 1px 6px;
            font-size: 11px;
            line-height: 1.3;
        }
    </style>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="registrations-header">
        <h1>Registrations</h1>

        <a href="{{ route('registrations.create') }}" class="btn btn-primary">
            Create New Registration
        </a>
    </div>

    <table class="table table-sm table-bordered">
        <thead>
        <tr>
            <th style="width:40px;">ID</th>
            <th style="width:70px;">Institution</th>
            <th>Student</th>
            <th>Programme</th>
            <th style="width:90px;">Year/Sem</th>
            <th style="width:110px;">Reg Date</th>
            <th style="width:85px;">Accommodation</th>
            <th style="width:90px;">Status</th>
            <th style="width:220px;">DTEF Status &amp; Response</th>
            <th style="width:130px;">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($registrations as $reg)
            @php
                $instCode = $reg->institution->short_code ?? '';
                $progCode = $reg->programme->code ?? '';
                $progName = $reg->programme->name ?? '';
                $showProgName = $progName && $progName !== $progCode;

                $regDate = optional($reg->registration_date)->format('Y-m-d');

                // Registration status pill
                $rawStatus   = trim($reg->registration_status ?? '');
                $statusUpper = strtoupper($rawStatus);
                $statusClass = 'pill-other';

                if ($statusUpper === 'REGISTERED') {
                    $statusClass = 'pill-approved';
                } elseif (in_array($statusUpper, ['PENDING','IN PROGRESS'])) {
                    $statusClass = 'pill-pending';
                } elseif (in_array($statusUpper, ['CANCELLED','WITHDRAWN'])) {
                    $statusClass = 'pill-rejected';
                }

                // DTEF status + button logic
                $dtefRaw   = trim($reg->dtef_status ?? '');
                $dtefUpper = strtoupper($dtefRaw);
                $dtefClass = 'pill-other';

                if (str_starts_with($dtefUpper, 'SENT')) {
                    $dtefClass = 'pill-dtef-sent';
                } elseif (str_starts_with($dtefUpper, 'ERROR')) {
                    $dtefClass = 'pill-dtef-error';
                } elseif (str_starts_with($dtefUpper, 'IMPORTED')) {
                    $dtefClass = 'pill-dtef-imported';
                }

                $isLocalMock = stripos($dtefRaw, 'local mock') !== false;
                $isRealSent  = str_starts_with($dtefUpper, 'SENT') && !$isLocalMock;
                $canSendToDtef = !$isRealSent;

                $textareaId = 'dtef-response-reg-'.$reg->id;

                $responsePretty = '';
                if ($reg->last_dtef_response) {
                    $decoded = json_decode($reg->last_dtef_response, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $responsePretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    } else {
                        $responsePretty = $reg->last_dtef_response;
                    }
                }

                $recipients = 'tpmotlogelwa@gov.bw,chandapiwa.mpuchane@neco.co.bw,thabiso.tlhobogang@neco.co.bw,jkrakgwasi@gov.bw,bbtsie@gov.bw,gmotupu@gov.bw';
                $subject = rawurlencode('DTEF SIS API issue – Registration ID '.$reg->id);
                $bodyLines = [
                    'Dear DTEF Team,',
                    '',
                    'We encountered an issue while sending a registration record from IDM Pedagre SIS.',
                    '',
                    'Registration details:',
                    '  - Registration ID: '.$reg->id,
                    '  - Institution: '.$instCode,
                    '  - Student: '.($reg->student->full_name ?? ''),
                    '  - Programme: '.$progCode.' - '.$progName,
                    '  - Study Year/Semester: '.$reg->study_year.'/'.$reg->study_semester,
                    '  - Registration Date: '.$regDate,
                    '',
                    'Last API response from DTEF:',
                    $responsePretty ?: '[no response stored]',
                    '',
                    'Kindly assist in checking this case.',
                    '',
                    'Regards,',
                    'IDM Pedagre SIS',
                ];
                $body = rawurlencode(implode("\n", $bodyLines));
                $mailtoHref = "mailto:$recipients?subject=$subject&body=$body";
            @endphp

            <tr>
                <td>{{ $reg->id }}</td>
                <td>{{ $instCode }}</td>
                <td>{{ $reg->student->full_name ?? '' }}</td>
                <td>
                    {{ $progCode }}
                    @if($showProgName)
                        - {{ $progName }}
                    @endif
                </td>
                <td>{{ $reg->study_year }}/{{ $reg->study_semester }}</td>
                <td>{{ $regDate }}</td>
                <td>{{ $reg->accommodation ? 'Yes' : 'No' }}</td>
                <td>
                    <span class="pill {{ $statusClass }}" title="{{ $rawStatus }}">
                        {{ $rawStatus ?: '—' }}
                    </span>
                </td>
                <td>
                    <span class="pill {{ $dtefClass }}" title="{{ $dtefRaw }}">
                        {{ $dtefRaw ?: 'Not Sent' }}
                    </span>

                    @if($responsePretty)
                        <div class="dtef-response-wrapper">
                            <small>Last response:</small>
                            <textarea id="{{ $textareaId }}" rows="4" readonly>{{ $responsePretty }}</textarea>
                            <div class="mt-1">
                                <button type="button"
                                        class="btn btn-xs btn-outline-secondary"
                                        onclick="copyResponse('{{ $textareaId }}')">
                                    Copy response
                                </button>
                                <a href="{{ $mailtoHref }}"
                                   class="btn btn-xs btn-outline-primary">
                                    Email DTEF
                                </a>
                            </div>
                        </div>
                    @endif
                </td>
                <td>
                    @if($canSendToDtef)
                        <form method="POST"
                              action="{{ route('registrations.sendToDtef', $reg) }}"
                              style="margin-bottom:4px;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                Send to DTEF
                            </button>
                        </form>
                    @else
                        <div>Already sent</div>
                    @endif

                    <a href="{{ route('results.create', ['registration_id' => $reg->id]) }}"
                       class="btn btn-outline-secondary btn-sm mt-1 w-100">
                        Capture Results
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10">No registrations yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <script>
        function copyResponse(id) {
            var ta = document.getElementById(id);
            if (!ta) return;
            ta.select();
            ta.setSelectionRange(0, ta.value.length);
            try {
                document.execCommand('copy');
                alert('Response copied to clipboard.');
            } catch (e) {
                alert('Unable to copy. Please copy manually.');
            }
        }
    </script>
@endsection
