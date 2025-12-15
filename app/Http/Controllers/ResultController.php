<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\Registration;
use App\Services\DtefClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(): View
    {
        $results = Result::with(['student', 'programme', 'items.module'])->get();

        return view('results.index', compact('results'));
    }

    /**
     * Show the create form for a specific registration.
     * Expect ?registration_id=XX in the query string.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $registrationId = $request->query('registration_id');

        if (! $registrationId) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'Please choose a registration to capture results for.');
        }

        $registration = Registration::with([
            'student.institution',
            'programme',
            'modules.module',
        ])->findOrFail($registrationId);

        // Extract the modules from the registration's module pivot
        $modules = $registration->modules
            ->map(function ($regModule) {
                return $regModule->module;
            })
            ->filter();

        return view('results.create', compact('registration', 'modules'));
    }

    /**
     * Store results for a specific registration.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'academic_year'   => ['nullable', 'string', 'max:20'],
            'exam_session'    => ['required', 'string', 'max:20'],
            'overall_status'  => ['required', 'string', 'max:20'],
            'gpa'             => ['nullable', 'numeric', 'min:0', 'max:4'],
            'remarks'         => ['nullable', 'string'],

            'marks'           => ['required', 'array'],
            'marks.*'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades'          => ['required', 'array'],
            'grades.*'        => ['nullable', 'string', 'max:5'],
        ]);

        $registration = Registration::with(['student', 'programme'])->findOrFail($data['registration_id']);
        $student      = $registration->student;
        $programme    = $registration->programme;

        $result = Result::create([
            'institution_id'  => $student->institution_id,
            'student_id'      => $student->id,
            'programme_id'    => $programme->id,
            'registration_id' => $registration->id,
            'study_year'      => $registration->study_year,
            'study_semester'  => $registration->study_semester,
            'academic_year'   => $data['academic_year'] ?? null,
            'exam_session'    => $data['exam_session'],
            'overall_status'  => $data['overall_status'],
            'gpa'             => $data['gpa'] ?? null,
            'remarks'         => $data['remarks'] ?? null,
            'dtef_status'     => 'Not Sent',
            'created_by'      => null,
        ]);

        // Only create items for modules that belong to this registration
        $registrationModules = $registration->modules()->with('module')->get();

        foreach ($registrationModules as $regModule) {
            $moduleId = $regModule->module_id;

            $mark  = $data['marks'][$moduleId]  ?? null;
            $grade = $data['grades'][$moduleId] ?? null;

            // If both mark and grade are empty, skip
            if ($mark === null && $grade === null) {
                continue;
            }

            $result->items()->create([
                'module_id'        => $moduleId,
                'mark'             => $mark,
                'grade'            => $grade,
                'remark'           => null,
                'is_supplementary' => false,
            ]);
        }

        return redirect()
            ->route('results.index')
            ->with('status', 'Result record created successfully.');
    }

    /**
     * Send a single result to DTEF.
     */
    public function sendToDtef(Result $result, DtefClient $client): RedirectResponse
    {
        try {
            $res = $client->sendResult($result);

            if ($res['ok']) {
                return redirect()
                    ->route('results.index')
                    ->with('status', 'DTEF: Result sent successfully for ' . ($result->student->full_name ?? ('Result #' . $result->id)));
            }

            return redirect()
                ->route('results.index')
                ->with('dtef_error', 'DTEF error (HTTP ' . $res['status'] . '): ' . json_encode($res['body']));
        } catch (\Throwable $e) {
            return redirect()
                ->route('results.index')
                ->with('dtef_error', 'Exception sending to DTEF: ' . $e->getMessage());
        }
    }

    /**
     * Send all pending / errored / mock results to DTEF in one batch.
     */
    public function sendPendingBatch(DtefClient $client): RedirectResponse
    {
        // All results that are NOT cleanly sent
        $results = Result::whereNull('dtef_status')
            ->orWhere('dtef_status', 'Not Sent')
            ->orWhere('dtef_status', 'Error')
            ->orWhere('dtef_status', 'Sent (local mock)')
            ->orWhere('dtef_status', 'LIKE', 'Imported%')
            ->with(['student', 'programme', 'items.module'])
            ->get();

        $ok   = 0;
        $fail = 0;

        foreach ($results as $result) {
            try {
                $res = $client->sendResult($result);
                if ($res['ok']) {
                    $ok++;
                } else {
                    $fail++;
                }
            } catch (\Throwable $e) {
                $fail++;
                $result->dtef_status        = 'Error';
                $result->last_dtef_response = json_encode(['exception' => $e->getMessage()]);
                $result->last_dtef_at       = now();
                $result->save();
            }
        }

        return redirect()
            ->route('results.index')
            ->with(
                'status',
                "DTEF batch complete: {$ok} sent successfully, {$fail} failed. Check row responses for details."
            );
    }
}
