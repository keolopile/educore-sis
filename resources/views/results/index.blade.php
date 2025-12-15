@extends('layouts.app')

@section('title', 'Results – Pedagre SIS')

@section('content')
    <style>
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .results-header h1 {
            margin: 0;
        }

        .results-actions-right .btn {
            margin-left: 4px;
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
        .pill-pass {
            background-color: #dcfce7;
            color: #166534;
        }
        .pill-fail {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .pill-abscond {
            background-color: #fef9c3;
            color: #854d0e;
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

        .module-badge {
            display: inline-block;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            padding: 1px 6px;
            font-size: 11px;
            margin: 1px 2px 1px 0;
            background-color: #f9fafb;
        }

        .btn-xs {
            padding: 1px 6px;
            font-size: 11px;
            line-height: 1.3;
        }
    </style>

    {{-- flash messages --}}
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if(session('error') || session('dtef_error'))
        <div class="alert alert-danger">
            {{ session('error') ?? session('dtef_error') }}
        </div>
    @endif

    <div class="results-header">
        <h1>Results</h1>

        <div class="results-actions-right">
            <a href="{{ route('results.create') }}"
               class="btn btn-outline-secondary btn-sm">
                Capture Results (select from Registrations)
            </a>

            <form action="{{ route('results.sendPendingBatch') }}"
                  method="POST"
                  style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    Send ALL pending to DTEF
                </button>
            </form>
        </div>
    </div>

    <table class="table table-sm table-bordered">
        <thead>
        <tr>
            <th style="width:40px;">ID</th>
            <th>Student</th>
            <th>Programme &amp; Modules</th>
            <th style="width:80px;">Year/Sem</th>
            <th style="width:100px;">Academic Year</th>
            <th style="width:70px;">Session</th>
            <th style="width:120px;">Overall Status</th>
            <th style="width:220px;">DTEF Status &amp; Response</th>
            <th style="width:120px;">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($results as $res)
            @php
                $student  = $res->student;
                $programme = $res->programme;
                $instCode = $res->institution->short_code ?? '';

                $progCode = $programme->code ?? '';
                $progName = $programme->name ?? '';
                $showProgName = $progName && $progName !== $progCode;

                // Overall status pill
                $overall   = trim($res->overall_status ?? '');
                $overallUp = strtoupper($overall);
                $overallClass = 'pill-other';

                if ($overallUp === 'PASS') {
                    $overallClass = 'pill-pass';
                } elseif (in_array($overallUp, ['FAIL','SUPP','REPEAT'])) {
                    $overallClass = 'pill-fail';
                } elseif (in_array($overallUp, ['ABSCOND','ABSCONDED'])) {
                    $overallClass = 'pill-abscond';
                }

                // DTEF status + button logic
                $dtefRaw   = trim($res->dtef_status ?? '');
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

                $textareaId = 'dtef-response-res-'.$res->id;

                $responsePretty = '';
                if ($res->last_dtef_response) {
                    $decoded = json_decode($res->last_dtef_response, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $responsePretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    } else {
                        $responsePretty = $res->last_dtef_response;
                    }
                }

                // Modules from items (assuming items are eager-loaded with module)
                $moduleCodes = [];
                if ($res->relationLoaded('items')) {
                    $moduleCodes = $res->items->pluck('module.code')->filter()->values()->all();
                }

                $recipients = 'tpmotlogelwa@gov.bw,chandapiwa.mpuchane@neco.co.bw,thabiso.tlhobogang@neco.co.bw,jkrakgwasi@gov.bw,bbtsie@gov.bw,gmotupu@gov.bw';
                $subject = rawurlencode('DTEF SIS API issue – Result ID '.$res->id);
                $bodyLines = [
                    'Dear DTEF Team,',
                    '',
                    'We encountered an issue while sending an exam result record from IDM Pedagre SIS.',
                    '',
                    'Result details:',
                    '  - Result ID: '.$res->id,
                    '  - Institution: '.$instCode,
                    '  - Student: '.($student->full_name ?? ''),
                    '  - Programme: '.$progCode.' - '.$progName,
                    '  - Year/Semester: '.$res->study_year.'/'.$res->study_semester,
                    '  - Academic Year: '.$res->academic_year,
                    '  - Session: '.$res->exam_session,
                    '  - Overall Status: '.$overall,
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
                <td>{{ $res->id }}</td>
                <td>{{ $student->full_name ?? '' }}</td>
                <td>
                    {{ $progCode }}
                    @if($showProgName)
                        - {{ $progName }}
                    @endif

                    @if(!empty($moduleCodes))
                        <div class="mt-1 small text-muted">
                            <strong>Modules:</strong>
                            @foreach($moduleCodes as $code)
                                <span class="module-badge">{{ $code }}</span>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td>{{ $res->study_year }}/{{ $res->study_semester }}</td>
                <td>{{ $res->academic_year }}</td>
                <td>{{ $res->exam_session }}</td>
                <td>
                    <span class="pill {{ $overallClass }}" title="{{ $overall }}">
                        {{ $overall ?: '—' }}
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
                              action="{{ route('results.sendToDtef', $res) }}"
                              style="margin:0;">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                Send to DTEF
                            </button>
                        </form>
                    @else
                        Already sent
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">No results yet.</td>
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
