<?php

namespace App\Imports;

use App\Models\Institution;
use App\Models\Programme;
use App\Models\Student;
use App\Models\Admission;
use App\Models\Registration;
use App\Models\Module;
use App\Models\RegistrationModule;
use App\Models\Result;
use App\Models\ResultItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DtefRegisterImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // First row is the header; we skip it.
        $header = $rows->first();
        $data   = $rows->slice(1);

        foreach ($data as $row) {
            $row = $row->toArray();

            // 0: NO
            // 1: INSTITUTION
            // 2: SURNAME
            // 3: FIRST NAME
            // 4: GENDER
            // 5: OMANG
            // 6: TR NO
            // 7: DATE OF BIRTH
            // 8: CONTACT NUMBER
            // 9: STU NUMBER
            // 10: PROGRAMME CODE
            // 11: PROGRAMME DESCRIPTION
            // 12: STUDY YEAR
            // 13: GPA
            // 14: LEVEL OF STUDY
            // 15: YEAR OF STUDY
            // 16: SEMESTER
            // 17: SPONSORSHIP START DATE
            // 18: COMPLETION DATE
            // 19: DATE OF REGISTRATION
            // 20: NO OF MODULES
            // 21: NO OF CREDIT
            // 22: MODEL CODES
            // 23: STUDENT STATUS
            // 24: ACCOM STATUS
            // 25: COMMENTS

            // 🔹 Normalise institution and basic fields
            $institutionRaw    = strtoupper(trim($row[1] ?? ''));   // e.g. "IDM - GABORONE"
            $surname           = trim($row[2] ?? '');
            $firstName         = trim($row[3] ?? '');
            $nationalId        = trim($row[5] ?? '');
            $trNumber          = trim($row[6] ?? '');   // TR NO
            $studentNumberRaw  = trim($row[9] ?? '');   // STU NUMBER

            // ✅ New: prefer STU NUMBER; if empty use TR NO; if still empty use OMANG
            $studentNumber     = $studentNumberRaw ?: $trNumber ?: $nationalId;

            $programmeCode     = trim($row[10] ?? '');
            $programmeName     = trim($row[11] ?? '');
            $studyYear         = (int)($row[15] ?? ($row[12] ?? 1)); // prefer YEAR OF STUDY, fall back to STUDY YEAR
            $semester          = (int)($row[16] ?? 1);
            $gpa               = is_numeric($row[13] ?? null) ? (float)$row[13] : null;
            $levelOfStudy      = trim($row[14] ?? '');
            $sponsorshipStart  = $this->parseExcelDate($row[17] ?? null);
            $completionDate    = $this->parseExcelDate($row[18] ?? null);
            $registrationDate  = $this->parseExcelDate($row[19] ?? null) ?? now();
            $modulesStr        = trim($row[22] ?? '');
            $studentStatus     = trim($row[23] ?? '') ?: 'Active';
            $accomStatus       = strtoupper(trim($row[24] ?? ''));

            // 🔹 Only import lines where institution starts with "IDM"
            if ($institutionRaw === '' || ! str_starts_with($institutionRaw, 'IDM')) {
                continue;
            }

            // All IDM campuses treated as one short code "IDM" for now
            $institutionCode = 'IDM';
            $institutionName = $institutionRaw; // full text, e.g. "IDM - GABORONE"

            // Skip blank lines (no OMANG and no name).
            if ($nationalId === '' && $surname === '' && $firstName === '') {
                continue;
            }

            // 1) Institution
            $institution = Institution::firstOrCreate(
                ['short_code' => $institutionCode],
                ['name' => $institutionName]
            );

            // 2) Student  (use updateOrCreate so re-import can fill missing student_number)
            $student = Student::updateOrCreate(
                ['national_id' => $nationalId],
                [
                    'institution_id' => $institution->id,
                    'student_number' => $studentNumber ?: null,
                    'first_name'     => $firstName,
                    'last_name'      => $surname,
                ]
            );

            // 3) Programme
            $durationYears = $this->guessDurationYears($levelOfStudy, $programmeName, $programmeCode);

            $programme = Programme::firstOrCreate(
                ['code' => $programmeCode],
                [
                    'institution_id' => $institution->id,
                    'name'           => $programmeName ?: $programmeCode,
                    'level'          => $levelOfStudy ?: null,
                    'duration_years' => $durationYears,
                ]
            );

            // 4) Admission (one per student + programme)
            $admission = Admission::firstOrCreate(
                [
                    'student_id'   => $student->id,
                    'programme_id' => $programme->id,
                ],
                [
                    'institution_id'           => $institution->id,
                    'commencement_date'        => $sponsorshipStart,
                    'expected_completion_date' => $completionDate,
                    'level_of_entry'           => $studyYear ?: 1,
                    'programme_cost'           => 0,
                    'admission_status'         => 'Approved',
                    'dtef_status'              => 'Imported',
                ]
            );

            // 5) Registration (per year/semester)
            $registration = Registration::firstOrCreate(
                [
                    'student_id'     => $student->id,
                    'programme_id'   => $programme->id,
                    'study_year'     => $studyYear ?: 1,
                    'study_semester' => $semester ?: 1,
                ],
                [
                    'institution_id'      => $institution->id,
                    'registration_date'   => $registrationDate,
                    'accommodation'       => $this->isOnCampus($accomStatus),
                    'registration_status' => $studentStatus ?: 'Active',
                    'academic_year'       => null, // can derive later if needed
                    'dtef_status'         => 'Imported from register',
                    'created_by'          => null,
                ]
            );

            // 6) Auto-create a Result "shell" for this registration
            $academicYear = null;
            if ($registrationDate instanceof \Carbon\Carbon) {
                $year         = $registrationDate->year;
                $academicYear = $year . '/' . ($year + 1);
            }

            // Very simple GPA → status rule; can be refined later
            $overallStatus = 'Pending';
            if (! is_null($gpa)) {
                $overallStatus = $gpa >= 2.0 ? 'Pass' : 'Fail';
            }

            $result = Result::firstOrCreate(
                [
                    'student_id'     => $student->id,
                    'programme_id'   => $programme->id,
                    'study_year'     => $studyYear ?: 1,
                    'study_semester' => $semester ?: 1,
                    'academic_year'  => $academicYear,
                    'exam_session'   => 'MAIN', // default session
                ],
                [
                    'institution_id' => $institution->id,
                    'overall_status' => $overallStatus,
                    'dtef_status'    => 'Not Sent',
                    'created_by'     => null,
                ]
            );

            // 7) Modules + attach to registration + result items
            if ($modulesStr !== '') {
                $codes = array_filter(array_map('trim', preg_split('/[,;]+/', $modulesStr)));

                foreach ($codes as $code) {
                    if ($code === '') {
                        continue;
                    }

                    // Ensure module exists
                    $module = Module::firstOrCreate(
                        [
                            'programme_id' => $programme->id,
                            'code'         => $code,
                        ],
                        [
                            'name' => $code,
                        ]
                    );

                    // Link to registration
                    RegistrationModule::firstOrCreate([
                        'registration_id' => $registration->id,
                        'module_id'       => $module->id,
                    ]);

                    // Create empty result line (mark/grade will be captured later)
                    ResultItem::firstOrCreate(
                        [
                            'result_id' => $result->id,
                            'module_id' => $module->id,
                        ],
                        [
                            'mark'    => null,
                            'grade'   => null,
                            'remarks' => null,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Convert Excel date or string into a Carbon instance or null.
     */
    protected function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \Carbon\Carbon || $value instanceof \DateTimeInterface) {
            return $value;
        }

        if (is_numeric($value)) {
            try {
                return \Carbon\Carbon::createFromTimestampUTC(($value - 25569) * 86400);
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function isOnCampus(string $accomStatus): bool
    {
        // Treat any of these as "on campus"
        return str_contains($accomStatus, 'ON CAMPUS')
            || str_contains($accomStatus, 'HOSTEL')
            || str_contains($accomStatus, 'YES');
    }

    /**
     * Guess a sensible duration_years value so the NOT NULL constraint is satisfied.
     */
    protected function guessDurationYears(?string $levelOfStudy, ?string $programmeName, ?string $programmeCode): int
    {
        $text = strtoupper(($levelOfStudy ?? '') . ' ' . ($programmeName ?? '') . ' ' . ($programmeCode ?? ''));

        if (str_contains($text, 'CERTIFICATE')) {
            return 1;
        }

        if (str_contains($text, 'HONOURS')) {
            return 4;
        }

        if (str_contains($text, 'DEGREE') || str_contains($text, 'BACHELOR')) {
            return 4;
        }

        if (str_contains($text, 'DIPLOMA')) {
            return 3;
        }

        return 3;
    }
}
