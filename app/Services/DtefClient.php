<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\Registration;
use App\Models\Result;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DtefClient
{
    /**
     * Whether to mock DTEF calls instead of hitting the real API.
     * Controlled by env DTEF_MOCK=true|false
     */
    protected function shouldMock(): bool
    {
        // Use filter_var so "false" really becomes false
        return filter_var(env('DTEF_MOCK', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Resolve base URL for DTEF depending on environment.
     * DTEF_ENV = test | live
     */
    protected function getBaseUrl(): string
    {
        $env = config('services.dtef.env', env('DTEF_ENV', 'test'));

        if ($env === 'live') {
            return env('DTEF_LIVE_BASE', 'https://tef.gov.bw');
        }

        return env('DTEF_TEST_BASE', 'https://tef2.gov.bw');
    }

    /**
     * Map institution short code → DTEF credentials.
     * Uses .env variables like DTEF_USERNAME_IDM, DTEF_PASSWORD_IDM etc.
     */
    protected function getCredentialsForInstitution(string $shortCode): array
    {
        $shortCode = strtoupper($shortCode);

        switch ($shortCode) {
            case 'IDM':
                return [
                    'username' => env('DTEF_USERNAME_IDM'),
                    'password' => env('DTEF_PASSWORD_IDM'),
                ];

            case 'AAFM':
                return [
                    'username' => env('DTEF_USERNAME_AAFM'),
                    'password' => env('DTEF_PASSWORD_AAFM'),
                ];

            case 'BUAN':
                return [
                    'username' => env('DTEF_USERNAME_BUAN'),
                    'password' => env('DTEF_PASSWORD_BUAN'),
                ];

            default:
                // fallback – use IDM creds by default
                return [
                    'username' => env('DTEF_USERNAME_IDM'),
                    'password' => env('DTEF_PASSWORD_IDM'),
                ];
        }
    }

    /**
     * Map our Institution model → the exact string DTEF expects.
     */
    protected function getDtefInstitutionName($institution): string
    {
        $short = strtoupper(trim($institution->short_code ?? ''));
        $city  = strtoupper(trim($institution->city ?? ''));

        // IDM Gaborone special case
        if ($short === 'IDM' && ($city === 'GABORONE' || $city === '')) {
            return 'INSTITUTE OF DEVELOPMENT MANAGEMENT';
            // If DTEF later insist:
            // return 'IDM - GABORONE';
        }

        // TODO: add other mappings here if DTEF gives you the exact labels
        return $short;
    }

    /**
     * Get CSRF token from DTEF.
     *
     * IMPORTANT: this matches your old PHP script:
     *  - NO Basic Auth here, plain GET to /rest/session/token
     *  - We only use Basic Auth on the POST request itself.
     */
    protected function getCsrfToken(string $baseUrl): string
    {
        $url = rtrim($baseUrl, '/') . '/rest/session/token';

        $response = Http::get($url);

        Log::info('DTEF CSRF token response (Laravel)', [
            'url'    => $url,
            'status' => $response->status(),
            'body'   => mb_substr($response->body(), 0, 300),
        ]);

        if (! $response->ok()) {
            throw new \RuntimeException(
                'Failed to get CSRF token (HTTP ' . $response->status() . ')'
            );
        }

        $body = trim($response->body());

        // If we somehow got HTML instead of a plain token, log it & throw a clean error
        if (str_starts_with($body, '<!DOCTYPE') || str_starts_with($body, '<html')) {
            Log::error('DTEF CSRF token HTML response in Laravel', [
                'status' => $response->status(),
                'body'   => mb_substr($body, 0, 500),
            ]);
            throw new \RuntimeException(
                'Failed to get CSRF token: HTML response from DTEF (check REST config / permissions).'
            );
        }

        return $body;
    }

    /* ============================================================
     *  ADMISSIONS
     * ============================================================
     */

    /**
     * Send an admission record to DTEF.
     */
    public function sendAdmission(Admission $admission): array
    {
        // Mock mode – don’t hit DTEF, just store a fake “sent” result
        if ($this->shouldMock()) {
            $admission->dtef_status        = 'Sent (local mock)';
            $admission->last_dtef_response = json_encode([
                'dtef'  => ['message' => 'Mock DTEF admission send in local environment'],
                'debug' => [],
            ]);
            $admission->last_dtef_at       = now();
            $admission->save();

            return [
                'ok'     => true,
                'status' => 200,
                'body'   => ['message' => 'Mock DTEF admission send in local environment'],
            ];
        }

        $institution = $admission->institution;
        $student     = $admission->student;
        $programme   = $admission->programme;

        $creds = $this->getCredentialsForInstitution($institution->short_code);
        $base  = $this->getBaseUrl();

        $institutionNameForDtef = $this->getDtefInstitutionName($institution);

        // National ID must be 9 digits (same as old script: stud_id)
        $idNumber = str_pad($student->national_id, 9, '0', STR_PAD_LEFT);

        // Program duration – match "program_duration" from old PHP code
        $programDuration = (int) ($programme->duration_years ?? 3);

        // ----------------------------
        // Commencement & completion
        // ----------------------------
        $startCarbon = $admission->commencement_date instanceof Carbon
            ? $admission->commencement_date
            : ($admission->commencement_date ? Carbon::parse($admission->commencement_date) : null);

        $endCarbon = $admission->expected_completion_date instanceof Carbon
            ? $admission->expected_completion_date
            : ($admission->expected_completion_date ? Carbon::parse($admission->expected_completion_date) : null);

        // If no completion date, approximate using programme duration
        if (! $endCarbon && $startCarbon && $programDuration > 0) {
            $endCarbon = $startCarbon->copy()->addYears($programDuration);
        }

        // DTEF insists completion must be a future date.
        // If it is today or in the past, push it forward.
        if ($endCarbon && ! $endCarbon->isFuture()) {
            $yearsAhead = $programDuration > 0 ? $programDuration : 1;
            $endCarbon  = now()->addYears($yearsAhead);
        }

        // Final strings in "DD MON YYYY" like "31 Jul 2027"
        $startDate = $startCarbon ? $startCarbon->format('d M Y') : null;
        $endDate   = $endCarbon ? $endCarbon->format('d M Y') : null;

        // Entry level MUST be numeric – force to integer
        $entryLevel = (int) ($admission->level_of_entry ?? 1);

        // Cost – must not be empty or zero
        $costRaw = $admission->programme_cost;
        if (is_numeric($costRaw) && (float) $costRaw > 0) {
            $cost = (float) $costRaw;
        } else {
            // Fallback placeholder so the API doesn’t reject the record.
            // Replace this with real costing logic later.
            $cost = 1.0;
        }

        // Get CSRF token (NO auth)
        $csrfToken = $this->getCsrfToken($base);

        // Field names copied from the working PHP script
        $payload = [
            "type" => [
                ["target_id" => "program_of_study"],
            ],
            "title" => [
                ["value" => $programme->name],
            ],
            "id" => [
                ["value" => $idNumber],
            ],
            "surname" => [
                ["value" => $student->last_name],
            ],
            "firstname" => [
                ["value" => trim($student->first_name . ' ' . ($student->middle_name ?? ''))],
            ],
            "institution" => [
                ["value" => $institutionNameForDtef],
            ],
            "institution_program_code" => [
                ["value" => $programme->code],
            ],
            "program_name" => [
                ["value" => $programme->name],
            ],
            "program_duration" => [
                ["value" => $programDuration],
            ],
            "start_date" => [
                ["value" => $startDate],
            ],
            "completion_date" => [
                ["value" => $endDate],
            ],
            "entry_level" => [
                ["value" => $entryLevel],
            ],
            "cost" => [
                ["value" => $cost],
            ],
        ];

        $url = rtrim($base, '/') . '/api/post/studentadmissions?_format=hal_json';

        $response = Http::withBasicAuth($creds['username'], $creds['password'])
            ->withHeaders([
                'X-CSRF-Token' => $csrfToken,
                'Content-Type' => 'application/hal+json',
            ])
            ->post($url, $payload);

        $rawBody = $response->json();
        if ($rawBody === null || $rawBody === []) {
            $rawBody = ['raw' => $response->body()];
        }

        $debug = [
            'student_national_id_sent' => $idNumber,
            'student_name_sent'        => trim(
                $student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name
            ),
            'institution_sent'         => $institutionNameForDtef,
            'programme_code_sent'      => $programme->code,
            'programme_name_sent'      => $programme->name,
            'start_date_sent'          => $startDate,
            'completion_date_sent'     => $endDate,
            'entry_level_sent'         => $entryLevel,
            'cost_sent'                => $cost,
        ];

        $body = [
            'dtef'  => $rawBody,
            'debug' => $debug,
        ];

        // Treat as success only if status is 2xx AND there is no "error" key in the DTEF part
        $dtefPart = is_array($rawBody) ? $rawBody : [];
        $isOk = $response->successful();
        if ($isOk && isset($dtefPart['error']) && $dtefPart['error']) {
            $isOk = false;
        }

        Log::info('DTEF admission response (Laravel)', [
            'url'      => $url,
            'status'   => $response->status(),
            'payload'  => $payload,
            'response' => $body,
        ]);

        $admission->dtef_status        = $isOk ? 'Sent' : 'Error';
        $admission->last_dtef_response = json_encode($body);
        $admission->last_dtef_at       = now();
        $admission->save();

        return [
            'ok'     => $isOk,
            'status' => $response->status(),
            'body'   => $body,
        ];
    }

    /* ============================================================
     *  REGISTRATIONS
     * ============================================================
     */

    /**
     * Send a registration record to DTEF.
     */
    public function sendRegistration(Registration $registration): array
    {
        // Mock mode
        if ($this->shouldMock()) {
            $registration->dtef_status        = 'Sent (local mock)';
            $registration->last_dtef_response = json_encode([
                'dtef'  => ['message' => 'Mock DTEF registration send in local environment'],
                'debug' => [],
            ]);
            $registration->last_dtef_at       = now();
            $registration->save();

            return [
                'ok'     => true,
                'status' => 200,
                'body'   => ['message' => 'Mock DTEF registration send in local environment'],
            ];
        }

        $institution = $registration->institution;
        $student     = $registration->student;
        $programme   = $registration->programme;

        $creds = $this->getCredentialsForInstitution($institution->short_code);
        $base  = $this->getBaseUrl();

        $institutionNameForDtef = $this->getDtefInstitutionName($institution);

        // National ID padded to 9 digits
        $idNumber = str_pad($student->national_id, 9, '0', STR_PAD_LEFT);

        // Semester start / end (end is a crude +4 months offset)
        $semStart = optional($registration->registration_date)->format('d M Y');
        $semEnd   = optional(optional($registration->registration_date)->copy()->addMonths(4))->format('d M Y');

        // Build modules comma list from RegistrationModules
        $modules = $registration->modules()
            ->with('module')
            ->get();

        $moduleCodes = $modules
            ->pluck('module.code')
            ->filter()
            ->implode(',');

        // CSRF without auth
        $csrfToken = $this->getCsrfToken($base);

        $payload = [
            "id" => [
                ['value' => $idNumber],
            ],
            "names" => [
                ['value' => trim($student->first_name . ' ' . ($student->middle_name ?? ''))],
            ],
            "surname" => [
                ['value' => $student->last_name],
            ],
            "prog_name" => [
                ['value' => $programme->name],
            ],
            "prog_code" => [
                ['value' => $programme->code],
            ],
            "inst" => [
                ['value' => $institutionNameForDtef],
            ],
            "campus" => [
                ['value' => $institution->city ?? 'MAIN'],
            ],
            "accomo" => [
                ['value' => $registration->accommodation ? 'Yes' : 'No'],
            ],
            "status" => [
                ['value' => $registration->registration_status],
            ],
            "study_year" => [
                ['value' => $registration->study_year],
            ],
            "study_semester" => [
                ['value' => $registration->study_semester],
            ],
            "sem_start_date" => [
                ['value' => $semStart],
            ],
            "sem_end_date" => [
                ['value' => $semEnd],
            ],
            "modules" => [
                ['value' => $moduleCodes],
            ],
        ];

        $url = rtrim($base, '/') . '/api/post/studentregistration?_format=hal_json';

        $response = Http::withBasicAuth($creds['username'], $creds['password'])
            ->withHeaders([
                'X-CSRF-Token' => $csrfToken,
                'Content-Type' => 'application/hal+json',
            ])
            ->post($url, $payload);

        $rawBody = $response->json();
        if ($rawBody === null || $rawBody === []) {
            $rawBody = ['raw' => $response->body()];
        }

        $debug = [
            'student_national_id_sent' => $idNumber,
            'student_name_sent'        => trim(
                $student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name
            ),
            'institution_sent'         => $institutionNameForDtef,
            'programme_code_sent'      => $programme->code,
            'programme_name_sent'      => $programme->name,
            'sem_start_date_sent'      => $semStart,
            'sem_end_date_sent'        => $semEnd,
            'modules_sent'             => $moduleCodes,
            'study_year_sent'          => $registration->study_year,
            'study_semester_sent'      => $registration->study_semester,
            'status_sent'              => $registration->registration_status,
            'accommodation_sent'       => $registration->accommodation ? 'Yes' : 'No',
        ];

        $body = [
            'dtef'  => $rawBody,
            'debug' => $debug,
        ];

        $dtefPart = is_array($rawBody) ? $rawBody : [];
        $isOk = $response->successful();
        if ($isOk && isset($dtefPart['error']) && $dtefPart['error']) {
            $isOk = false;
        }

        Log::info('DTEF registration response (Laravel)', [
            'url'      => $url,
            'status'   => $response->status(),
            'payload'  => $payload,
            'response' => $body,
        ]);

        $registration->dtef_status        = $isOk ? 'Sent' : 'Error';
        $registration->last_dtef_response = json_encode($body);
        $registration->last_dtef_at       = now();
        $registration->save();

        return [
            'ok'     => $isOk,
            'status' => $response->status(),
            'body'   => $body,
        ];
    }

    /* ============================================================
     *  RESULTS
     * ============================================================
     */

    /**
     * Send an exam result record to DTEF.
     */
    public function sendResult(Result $result): array
    {
        // Mock mode
        if ($this->shouldMock()) {
            $result->dtef_status        = 'Sent (local mock)';
            $result->last_dtef_response = json_encode([
                'dtef'  => ['message' => 'Mock DTEF result send in local environment'],
                'debug' => [],
            ]);
            $result->last_dtef_at       = now();
            $result->save();

            return [
                'ok'     => true,
                'status' => 200,
                'body'   => ['message' => 'Mock DTEF result send in local environment'],
            ];
        }

        $institution = $result->institution;
        $student     = $result->student;
        $programme   = $result->programme;

        $creds = $this->getCredentialsForInstitution($institution->short_code);
        $base  = $this->getBaseUrl();

        $institutionNameForDtef = $this->getDtefInstitutionName($institution);

        // ID + modules list from ResultItems
        $idNumber = str_pad($student->national_id, 9, '0', STR_PAD_LEFT);

        $items = $result->items()
            ->with('module')
            ->get();

        $moduleCodes = $items
            ->pluck('module.code')
            ->filter()
            ->implode(',');

        // GPA not yet stored – can be added later
        $gpa = $result->gpa ?? null;

        // CSRF without auth
        $csrfToken = $this->getCsrfToken($base);

        $payload = [
            "id" => [
                ['value' => $idNumber],
            ],
            "names" => [
                ['value' => trim($student->first_name . ' ' . ($student->middle_name ?? ''))],
            ],
            "surname" => [
                ['value' => $student->last_name],
            ],
            "prog_name" => [
                ['value' => $programme->name],
            ],
            "prog_code" => [
                ['value' => $programme->code],
            ],
            "inst" => [
                ['value' => $institutionNameForDtef],
            ],
            "campus" => [
                ['value' => $institution->city ?? 'MAIN'],
            ],
            "study_year" => [
                ['value' => $result->study_year],
            ],
            "study_semester" => [
                ['value' => $result->study_semester],
            ],
            "academic_year" => [
                ['value' => $result->academic_year],
            ],
            "session" => [
                ['value' => $result->exam_session],
            ],
            "overall_status" => [
                ['value' => $result->overall_status],
            ],
            "gpa" => [
                ['value' => $gpa],
            ],
            "modules" => [
                ['value' => $moduleCodes],
            ],
        ];

        $url = rtrim($base, '/') . '/api/post/studentresults?_format=hal_json';

        $response = Http::withBasicAuth($creds['username'], $creds['password'])
            ->withHeaders([
                'X-CSRF-Token' => $csrfToken,
                'Content-Type' => 'application/hal+json',
            ])
            ->post($url, $payload);

        $rawBody = $response->json();
        if ($rawBody === null || $rawBody === []) {
            $rawBody = ['raw' => $response->body()];
        }

        $debug = [
            'student_national_id_sent' => $idNumber,
            'student_name_sent'        => trim(
                $student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name
            ),
            'institution_sent'         => $institutionNameForDtef,
            'programme_code_sent'      => $programme->code,
            'programme_name_sent'      => $programme->name,
            'modules_sent'             => $moduleCodes,
            'study_year_sent'          => $result->study_year,
            'study_semester_sent'      => $result->study_semester,
            'academic_year_sent'       => $result->academic_year,
            'session_sent'             => $result->exam_session,
            'overall_status_sent'      => $result->overall_status,
            'gpa_sent'                 => $gpa,
        ];

        $body = [
            'dtef'  => $rawBody,
            'debug' => $debug,
        ];

        $dtefPart = is_array($rawBody) ? $rawBody : [];
        $isOk = $response->successful();
        if ($isOk && isset($dtefPart['error']) && $dtefPart['error']) {
            $isOk = false;
        }

        Log::info('DTEF results response (Laravel)', [
            'url'      => $url,
            'status'   => $response->status(),
            'payload'  => $payload,
            'response' => $body,
        ]);

        $result->dtef_status        = $isOk ? 'Sent' : 'Error';
        $result->last_dtef_response = json_encode($body);
        $result->last_dtef_at       = now();
        $result->save();

        return [
            'ok'     => $isOk,
            'status' => $response->status(),
            'body'   => $body,
        ];
    }
}
