<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Student;
use App\Models\Programme;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Services\DtefClient;


class RegistrationController extends Controller
{
    public function index(): View
    {
        $registrations = Registration::with(['institution', 'student', 'programme', 'modules.module'])->get();

        return view('registrations.index', compact('registrations'));
    }

    public function create(): View
    {
        $students   = Student::with('institution')->orderBy('student_number')->get();
        $programmes = Programme::with('institution')->orderBy('code')->get();
        $modules    = Module::with('programme')->orderBy('code')->get();

        return view('registrations.create', compact('students', 'programmes', 'modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'        => ['required', 'exists:students,id'],
            'programme_id'      => ['required', 'exists:programmes,id'],
            'study_year'        => ['required', 'integer', 'min:1', 'max:10'],
            'study_semester'    => ['required', 'integer', 'min:1', 'max:4'],
            'accommodation'     => ['nullable', 'boolean'],
            'registration_date' => ['required', 'date'],
            'module_ids'        => ['required', 'array', 'min:1'],
            'module_ids.*'      => ['exists:modules,id'],
        ]);

        $student   = Student::findOrFail($data['student_id']);
        $programme = Programme::findOrFail($data['programme_id']);

        $registration = Registration::create([
            'institution_id'      => $student->institution_id,
            'student_id'          => $student->id,
            'programme_id'        => $programme->id,
            'study_year'          => $data['study_year'],
            'study_semester'      => $data['study_semester'],
            'accommodation'       => $request->boolean('accommodation'),
            'registration_date'   => $data['registration_date'],
            'registration_status' => 'Active',
            'dtef_status'         => 'Not Sent',
            'created_by'          => null,
        ]);

        foreach ($data['module_ids'] as $mid) {
            $registration->modules()->create([
                'module_id'   => $mid,
                'is_repeated' => false,
            ]);
        }

        return redirect()
            ->route('registrations.index')
            ->with('status', 'Registration created successfully.');
    }

    public function sendToDtef(Registration $registration, DtefClient $dtefClient): RedirectResponse
{
    try {
        $result = $dtefClient->sendRegistration($registration);

        if ($result['ok']) {
            return redirect()
                ->route('registrations.index')
                ->with('status', "Registration {$registration->id} sent to DTEF successfully.");
        }

        return redirect()
            ->route('registrations.index')
            ->with('error', "DTEF error (HTTP {$result['status']}): " . json_encode($result['body']));
    } catch (\Throwable $e) {
        return redirect()
            ->route('registrations.index')
            ->with('error', 'Exception sending registration to DTEF: ' . $e->getMessage());
    }
}



public function sendAllToDtef(DtefClient $client)
{
    $regs = Registration::whereNull('dtef_status')
        ->orWhereIn('dtef_status', ['Not Sent', 'Imported', 'Imported (results)'])
        ->get();

    $sent = 0;
    $failed = 0;

    foreach ($regs as $reg) {
        try {
            $res = $client->sendRegistration($reg);
            $res['ok'] ? $sent++ : $failed++;
        } catch (\Throwable $e) {
            $failed++;
        }
    }

    return redirect()
        ->route('registrations.index')
        ->with('status', "Registrations: {$sent} sent, {$failed} failed.");
}





}
