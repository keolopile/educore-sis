@extends('layouts.app')

@section('title', 'Admissions')

@section('content')
    <style>
        .admissions-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .admissions-header h1 {
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

    <div class="admissions-header">
        <h1>Admissions</h1>

        <a href="{{ route('admissions.create') }}" class="btn btn-primary">
            Create New Admission
        </a>
    </div>

    <table class="table table-sm table-bordered" cellpadding="0" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th style="width:40px;">ID</th>
            <th style="width:70px;">Institution</th>
            <th>Student</th>
            <th>Programme</th>
            <th style="width:70px;">Level of Entry</th>
            <th style="width:110px;">Commencement</th>
            <th style="width:110px;">Completion</th>
            <th style="width:80px;">Status</th>
            <th style="width:220px;">DTEF Status &amp; Response</th>
            <th style="width:120px;">Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($admissions as $adm)
            @php
                $instCode = $adm->institution->short_code ?? '';
                $progCode = $adm->programme->code ?? '';
                $progName = $adm->programme->name ?? '';

                $showProgName = $progName && $progName !== $progCode;
                $commence = optional($adm->commencement_date)->format('Y-m-d');
                $complete = optional($adm->expected_completion_date)->format('Y-m-d');

                // Admission status pill
                $rawStatus   = trim($adm->admission_status ?? '');
                $statusUpper = strtoupper($rawStatus);
                $statusClass = 'pill-other';

                if ($statusUpper === 'APPROVED') {
                    $statusClass = 'pill-approved';
                } elseif (in_array($statusUpper, ['PENDING','IN PROGRESS'])) {
                    $statusClass = 'pill-pending';
                } elseif (in_array($statusUpper, ['REJECTED','DECLINED'])) {
                    $statusClass = 'pill-rejected';
                }

                // DTEF status pill + logic for button visibility
                $dtefRaw   = trim($adm->dtef_status ?? '');
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
                // show button in all cases except real sent
                $canSendToDtef = !$isRealSent;

                $textareaId = 'dtef-response-adm-'.$adm->id;

                // Pretty-print last response if JSON
                $responsePretty = '';
                if ($adm->last_dtef_response) {
                    $decoded = json_decode($adm->last_dtef_response, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $responsePretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    } else {
                        $responsePretty = $adm->last_dtef_response;
                    }
                }

                // Email DTEF link
                $recipients = 'tpmotlogelwa@gov.bw,chandapiwa.mpuchane@neco.co.bw,thabiso.tlhobogang@neco.co.bw,jkrakgwasi@gov.bw,bbtsie@gov.bw,gmotupu@gov.bw';
                $subject = rawurlencode('DTEF SIS API issue – Admission ID '.$adm->id);
                $bodyLines = [
                    'Dear DTEF Team,',
                    '',
                    'We encountered an issue while sending an admission record from IDM Pedagre SIS.',
                    '',
                    'Admission details:',
                    '  - Admission ID: '.$adm->id,
                    '  - Institution: '.$instCode,
                    '  - Student: '.($adm->student->full_name ?? ''),
                    '  - Programme: '.$progCode.' - '.$progName,
                    '  - Commencement: '.$commence,
                    '  - Completion: '.$complete,
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
                <td>{{ $adm->id }}</td>
                <td>{{ $instCode }}</td>
                <td>{{ $adm->student->full_name ?? '' }}</td>
                <td>
                    {{ $progCode }}
                    @if($showProgName)
                        - {{ $progName }}
                    @endif
                </td>
                <td>{{ $adm->level_of_entry }}</td>
                <td>{{ $commence }}</td>
                <td>{{ $complete }}</td>
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
                        <form action="{{ route('admissions.sendToDtef', $adm) }}"
                              method="POST" style="margin:0;">
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
                <td colspan="10">No admissions yet.</td>
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
