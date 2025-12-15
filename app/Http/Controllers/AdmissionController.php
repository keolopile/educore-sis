<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Programme;
use App\Models\Admission;
use App\Services\DtefClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function index(): View
    {
        $admissions = Admission::with(['institution', 'student', 'programme'])->get();

        return view('admissions.index', compact('admissions'));
    }

    public function sendToDtef(Admission $admission, DtefClient $dtefClient): RedirectResponse
    {
        try {
            $result = $dtefClient->sendAdmission($admission);

            if ($result['ok']) {
                return redirect()
                    ->route('admissions.index')
                    ->with('status', "Admission {$admission->id} sent to DTEF successfully.");
            }

            return redirect()
                ->route('admissions.index')
                ->with('error', "DTEF error (HTTP {$result['status']}): " . json_encode($result['body']));
        } catch (\Throwable $e) {
            return redirect()
                ->route('admissions.index')
                ->with('error', 'Exception sending to DTEF: ' . $e->getMessage());
        }
    }

    public function create(): \Illuminate\View\View
{
    // For now, load all students & programmes.
    // Later you can filter by institution or add search.
    $students   = Student::with('institution')->orderBy('student_number')->get();
    $programmes = Programme::with('institution')->orderBy('code')->get();

    return view('admissions.create', compact('students', 'programmes'));
}

public function store(Request $request): \Illuminate\Http\RedirectResponse
{
    // Validate form input
    $data = $request->validate([
        'student_id'               => ['required', 'exists:students,id'],
        'programme_id'             => ['required', 'exists:programmes,id'],
        'commencement_date'        => ['required', 'date'],
        'expected_completion_date' => ['required', 'date', 'after_or_equal:commencement_date'],
        'level_of_entry'           => ['required', 'integer', 'min:1', 'max:10'],
        'programme_cost'           => ['nullable', 'numeric', 'min:0'],
        'admission_status'         => ['required', 'in:Pending,Approved,Rejected'],
    ]);

    $student   = Student::findOrFail($data['student_id']);
    $programme = Programme::findOrFail($data['programme_id']);

    Admission::create([
        'institution_id'          => $student->institution_id, // derive from student
        'student_id'              => $student->id,
        'programme_id'            => $programme->id,
        'commencement_date'       => $data['commencement_date'],
        'expected_completion_date'=> $data['expected_completion_date'],
        'level_of_entry'          => $data['level_of_entry'],
        'programme_cost'          => $data['programme_cost'] ?? null,
        'admission_status'        => $data['admission_status'],
        'dtef_status'             => 'Not Sent',
        'created_by'              => null, // later you can use auth()->id()
    ]);

    return redirect()
        ->route('admissions.index')
        ->with('status', 'Admission created successfully.');
}



public function sendAllToDtef(DtefClient $client)
{
    $admissions = Admission::whereNull('dtef_status')
        ->orWhereIn('dtef_status', ['Not Sent', 'Imported', 'Imported (results)'])
        ->get();

    $sent = 0;
    $failed = 0;

    foreach ($admissions as $adm) {
        try {
            $res = $client->sendAdmission($adm);
            $res['ok'] ? $sent++ : $failed++;
        } catch (\Throwable $e) {
            $failed++;
        }
    }

    return redirect()
        ->route('admissions.index')
        ->with('status', "Admissions: {$sent} sent, {$failed} failed.");
}



}
