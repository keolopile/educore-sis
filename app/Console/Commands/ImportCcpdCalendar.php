<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CpdDomain;
use App\Models\CpdCourse;
use App\Models\CpdSession;
use App\Services\MoodleClient;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportCcpdCalendar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Optional {file} argument lets you override the path.
     */
    protected $signature = 'cpd:import-calendar {file?}';

    /**
     * The console command description.
     */
    protected $description = 'Import CCPD calendar Excel into CPD tables';

    /**
     * Moodle client for auto-creating/linking Moodle courses.
     */
    protected MoodleClient $moodle;

    public function __construct(MoodleClient $moodle)
    {
        parent::__construct();
        $this->moodle = $moodle;
    }

    public function handle(): int
    {
        $fileArg = $this->argument('file');

        $path = $fileArg
            ? base_path($fileArg)
            : storage_path('app/ccpd/ccpd_calendar_2025_2026.xlsx');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $this->info("Loading calendar from: {$path}");

        $spreadsheet = IOFactory::load($path);
        $sheet       = $spreadsheet->getSheet(0);

        // In your file:
        // Row 4 = header row:
        //  A:#, B:NAME OF COURSES, C:DISCIPLINE, D:DURATION,
        //  E:APRIL, F:MAY, G:JUNE, H:JULY, I:AUG, J:SEPT, K:OCT, L:NOV, M:DEC,
        //  N:JAN, O:FEB, P:MAR, Q:FEES, R:MEALS
        $headerRow    = 4;
        $firstDataRow = 5;

        // Column indexes are 1-based in PhpSpreadsheet
        $colName       = 2;
        $colDiscipline = 3;
        $colDuration   = 4;
        $colFees       = 17;

        // Calendar year: APR–DEC 2025, JAN–MAR 2026
        $monthSpecs = [
            // col => [ monthNumber, year ]
            5  => ['month' => 4,  'year' => 2025], // APRIL
            6  => ['month' => 5,  'year' => 2025], // MAY
            7  => ['month' => 6,  'year' => 2025], // JUNE
            8  => ['month' => 7,  'year' => 2025], // JULY
            9  => ['month' => 8,  'year' => 2025], // AUG
            10 => ['month' => 9,  'year' => 2025], // SEPT
            11 => ['month' => 10, 'year' => 2025], // OCT
            12 => ['month' => 11, 'year' => 2025], // NOV
            13 => ['month' => 12, 'year' => 2025], // DEC
            14 => ['month' => 1,  'year' => 2026], // JAN
            15 => ['month' => 2,  'year' => 2026], // FEB
            16 => ['month' => 3,  'year' => 2026], // MAR
        ];

        $highestRow = $sheet->getHighestRow();

        $createdCourses  = 0;
        $createdSessions = 0;

        DB::beginTransaction();

        try {
            for ($row = $firstDataRow; $row <= $highestRow; $row++) {
                $name = trim((string) $sheet->getCellByColumnAndRow($colName, $row)->getValue());

                // Stop if we hit an empty row (no course name)
                if ($name === '') {
                    continue;
                }

                $discipline = trim((string) $sheet->getCellByColumnAndRow($colDiscipline, $row)->getValue());
                $duration   = trim((string) $sheet->getCellByColumnAndRow($colDuration, $row)->getValue());
                $feesRaw    = trim((string) $sheet->getCellByColumnAndRow($colFees, $row)->getValue());

                // Convert "BIRM" into a domain
                if ($discipline === '') {
                    $discipline = 'General';
                }

                $domainSlug = Str::slug(strtolower($discipline));
                $domain     = CpdDomain::firstOrCreate(
                    ['slug' => $domainSlug],
                    [
                        'name'        => $discipline,
                        'description' => 'Imported from CCPD calendar',
                        'is_active'   => true,
                    ]
                );

                // Create / update course
                // Use discipline + row number to generate a stable code
                $rowNumber = (int) $sheet->getCellByColumnAndRow(1, $row)->getValue(); // "#" column
                $code      = strtoupper($discipline) . '-' . str_pad($rowNumber, 3, '0', STR_PAD_LEFT);

                // Duration: "5 days" => 5
                $durationDays = null;
                if (preg_match('/(\d+)/', $duration, $m)) {
                    $durationDays = (int) $m[1];
                }

                // Fees: "P4,250.00" => 4250.00
                $defaultPrice = null;
                if ($feesRaw !== '') {
                    $clean = preg_replace('/[^\d.]/', '', $feesRaw);
                    if ($clean !== '') {
                        $defaultPrice = (float) $clean;
                    }
                }

                $course = CpdCourse::firstOrCreate(
                    ['code' => $code],
                    [
                        'cpd_domain_id'     => $domain->id,
                        'title'             => $name,
                        'short_description' => null,
                        'full_description'  => null,
                        'duration_days'     => $durationDays,
                        'cpd_points'        => null,
                        'default_price'     => $defaultPrice,
                        'currency'          => 'BWP',
                        'is_active'         => true,
                    ]
                );

                if ($course->wasRecentlyCreated) {
                    $createdCourses++;
                }

                // Now loop through month columns and create sessions
                foreach ($monthSpecs as $colIndex => $spec) {
                    $cellValue = $sheet->getCellByColumnAndRow($colIndex, $row)->getValue();
                    $cellValue = trim((string) $cellValue);

                    if ($cellValue === '' || $cellValue === '-' || $cellValue === '–') {
                        continue;
                    }

                    // Normalise spaces: "09 -13" -> "09-13"
                    $cellValue = preg_replace('/\s+/', '', $cellValue);

                    // Expect formats like "09-13", "9-13"
                    if (! preg_match('/^(\d{1,2})-(\d{1,2})$/', $cellValue, $m)) {
                        // If format is unexpected, just skip silently
                        $this->warn("  Skipping weird date '{$cellValue}' on row {$row}, col {$colIndex}");
                        continue;
                    }

                    $startDay = (int) $m[1];
                    $endDay   = (int) $m[2];

                    try {
                        $startDate = Carbon::create(
                            $spec['year'],
                            $spec['month'],
                            $startDay
                        )->startOfDay();

                        $endDate = Carbon::create(
                            $spec['year'],
                            $spec['month'],
                            $endDay
                        )->startOfDay();
                    } catch (\Exception $e) {
                        $this->warn("  Invalid date for row {$row}, month {$spec['month']}: {$cellValue}");
                        continue;
                    }

                    // You can later derive different modes per discipline if needed
                    $deliveryMode = 'online';

                    // Avoid duplicates: same course + start_date
                    $session = CpdSession::updateOrCreate(
                        [
                            'cpd_course_id' => $course->id,
                            'start_date'    => $startDate->toDateString(),
                        ],
                        [
                            'end_date'        => $endDate->toDateString(),
                            'delivery_mode'   => $deliveryMode,
                            'location'        => 'Gaborone Campus', // or blank
                            'price'           => $defaultPrice,
                            'currency'        => 'BWP',
                            'capacity'        => 30,
                            'seats_taken'     => 0,
                            'moodle_course_id'=> null,
                            'status'          => 'open',
                        ]
                    );

                    if ($session->wasRecentlyCreated) {
                        $createdSessions++;
                    }

                    // 🔗 NEW: ensure Moodle course exists for online/hybrid runs
                    if (in_array($deliveryMode, ['online', 'hybrid'])) {
                        try {
                            $this->moodle->ensureMoodleCourseForCpdCourse($course);
                        } catch (\Throwable $e) {
                            $this->warn("  Moodle course creation failed for {$course->code}: {$e->getMessage()}");
                        }
                    }
                }
            }

            DB::commit();

            $this->info("Import complete.");
            $this->info("  New courses created : {$createdCourses}");
            $this->info("  New sessions created: {$createdSessions}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
