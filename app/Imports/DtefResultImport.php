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
use Carbon\Carbon;

class DtefResultImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // First row is the header
        $header = $rows->first();
        $data   = $rows->slice(1);

        foreach ($data as $row) {
            $row = $row->toArray();

            // Column mapping based on your IDM sheet:
            // 0: NO
            // 1: INSTITUTION
            // 2: SURNAME
            // 3: FIRST NAME
            // 4: GENDER
            // 5: OMANG
            // 6: TR NO
            // 7: DATE OF BIRTH
            // 8: CONTACT NUMBER
            // 9: PROGRAMME CODE
            // 10: PROGRAMME DESCRIPTION
            // 11: LEVEL OF STUDY
            // 12: YEAR OF STUDY
            // 13: SEMESTER
            // 14: COMPLETION DATE
            // 15: DATE OF REGISTRATION
            // 16: NO OF MODULES
            // 17: NO OF CREDIT
            // 18: MODELS ENROLLED FOR
            // 19: TERM ENDING
            // 20: MODELS PASSED
            // 21: RETAKE MODULES
            // 22: SUPPLEMENTARY/RESIT MODULES
            // 23: ACADEMIC OUTCOME

            $institutionRaw   = strtoupper(trim($row[1] ?? ''));  // "IDM - GABORONE"
            $surname          = trim($row[2] ?? '');
            $firstName        = trim($row[3] ?? '');
            $gender           = strtoupper(trim($row[4] ?? ''));
            $nationalId       = trim($row[5] ?? '');
            $trNo             = trim($row[6] ?? '');
            $dobRaw           = trim($row[7] ?? '');
            $contactNumber    = trim($row[8] ?? '');
            $programmeCode    = trim($row[9] ?? '');
            $programmeName    = trim($row[10] ?? '');
            $levelOfStudy     = trim($row[11] ?? '');        // "DEGREE", "DIPLOMA", etc.
            $yearOfStudy      = (int)($row[12] ?? 1);
            $semester         = (int)($row[13] ?? 1);
            $completionDate   = $this->parseExcelDate($row[14] ?? null);
            $registrationDate = $this->parseExcelDate($row[15] ?? null);
            $numModules       = is_numeric($row[16] ?? null) ? (int)$row[16] : null;
            $numCredits       = is_numeric($row[17] ?? null) ? (int)$row[17] : null;
            $modulesEnrolled  = trim($row[18] ?? '');
            $termEnding       = $this->parseExcelDate($row[19] ?? null);
            $modulesPassedRaw = trim($row[20] ?? '');
            $retakeModulesRaw = trim($row[21] ?? '');
            $suppModulesRaw   = trim($row[22] ?? '');
            $academicOutcome  = trim($row[23] ?? '');        // ABSCOND / PROCEED / etc.

            // Only import IDM rows for now
            if ($institutionRaw === '' || ! str_starts_with($institutionRaw, 'IDM')) {
                continue;
            }

            // Skip totally blank lines
            if ($nationalId === '' && $surname === '' && $firstName === '') {
                continue;
            }

            // Canonical institution data
            $institutionCode = 'IDM';            // one IDM institution for now
            $institutionName = $institutionRaw;  // full label

            // 1) Institution
            $institution = Institution::firstOrCreate(
                ['short_code' => $institutionCode],
                ['name' => $institutionName]
            );

            // 2) Student
            $student = Student::firstOrCreate(
                ['national_id' => $nationalId],
                [
                    'institution_id' => $institution->id,
                    'student_number' => null, // not provided in this sheet
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

            // 4) Admission (keep it in sync; one per student+programme)
            $admission = Admission::firstOrCreate(
                [
                    'student_id'   => $student->id,
                    'programme_id' => $programme->id,
                ],
                [
                    'institution_id'           => $institution->id,
                    'commencement_date'        => $registrationDate,
                    'expected_completion_date' => $completionDate,
                    'level_of_entry'           => $yearOfStudy ?: 1,
                    'programme_cost'           => 0,
                    'admission_status'         => 'Approved',
                    'dtef_status'              => 'Imported (results)',
                ]
            );

            // 5) Registration (per year/semester)
            $academicYear = $this->deriveAcademicYear($termEnding ?? $registrationDate);

            $registration = Registration::firstOrCreate(
                [
                    'student_id'     => $student->id,
                    'programme_id'   => $programme->id,
                    'study_year'     => $yearOfStudy ?: 1,
                    'study_semester' => $semester ?: 1,
                ],
                [
                    'institution_id'      => $institution->id,
                    'registration_date'   => $registrationDate,
                    'accommodation'       => false,
                    'registration_status' => 'Active',
                    'academic_year'       => $academicYear,
                    'sem_start_date'      => $registrationDate,
                    'sem_end_date'        => $termEnding,
                    'sponsor_start_date'  => $registrationDate,
                    'sponsor_end_date'    => $completionDate,
                    'dtef_status'         => 'Imported from results',
                ]
            );

            // 6) Build module lists from the string columns
            $enrolledCodes = $this->splitCodes($modulesEnrolled);
            $passedCodes   = $this->splitCodes($modulesPassedRaw);
            $retakeCodes   = $this->splitCodes($retakeModulesRaw);
            $suppCodes     = $this->splitCodes($suppModulesRaw);

            $uniqueEnrolled = array_values(array_unique($enrolledCodes));

            // Very basic counts
            $passedCount = $numModules !== null
                ? ($numModules - count($retakeCodes) - count($suppCodes))
                : count($passedCodes);

            if ($passedCount < 0) {
                $passedCount = null;
            }

            $failedCount = ($numModules !== null && $passedCount !== null)
                ? max($numModules - $passedCount, 0)
                : null;

            // 7) Create / update the Result header
            $result = Result::firstOrCreate(
                [
                    'student_id'     => $student->id,
                    'programme_id'   => $programme->id,
                    'study_year'     => $yearOfStudy ?: 1,
                    'study_semester' => $semester ?: 1,
                    'academic_year'  => $academicYear,
                    'exam_session'   => 'MAIN',
                ],
                [
                    'institution_id'        => $institution->id,
                    'overall_status'        => $academicOutcome ?: 'Pending',
                    'etp_academic_outcome'  => $academicOutcome ?: null,
                    'sponsorship_start_date'=> $registrationDate,
                    'sponsorship_end_date'  => $completionDate,
                    'passed_modules'        => $passedCount,
                    'num_failed_modules'    => $failedCount,
                    'modules_list'          => implode(',', $uniqueEnrolled),
                    'modules_passed'        => implode(',', $passedCodes),
                    'repeated_modules'      => implode(',', $retakeCodes),
                    'failed_modules'        => null, // could be derived later
                    'dtef_status'           => 'Not Sent',
                ]
            );

            // 8) Ensure modules, registration_modules and result_items
            foreach ($uniqueEnrolled as $code) {
                if ($code === '') {
                    continue;
                }

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

                // Result item shell
                $item = ResultItem::firstOrCreate(
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

                // Try to assign result_flag if we can infer something
                $flag = null;
                if (in_array($code, $retakeCodes, true) || in_array($code, $suppCodes, true)) {
                    $flag = 's'; // supplementary / resit
                } elseif (in_array($code, $passedCodes, true)) {
                    $flag = 'p'; // pass
                }

                if ($flag !== null) {
                    $item->result_flag = $flag;
                    $item->is_repeat   = in_array($code, $retakeCodes, true);
                    $item->save();
                }
            }
        }
    }

    protected function parseExcelDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_numeric($value)) {
            try {
                return Carbon::createFromTimestampUTC(($value - 25569) * 86400);
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function deriveAcademicYear(?Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        // Simple rule: academic year = Y / Y+1 based on the calendar year
        $year = $date->year;

        return $year . '/' . ($year + 1);
    }

    protected function splitCodes(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === '0') {
            return [];
        }

        $parts = preg_split('/[,;]+/', $raw);
        $codes = [];

        foreach ($parts as $part) {
            $code = trim($part);
            if ($code === '' || $code === '0') {
                continue;
            }
            // Normalise internal spaces
            $codes[] = preg_replace('/\s+/', ' ', $code);
        }

        return $codes;
    }

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
