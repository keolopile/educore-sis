<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Sponsorship window
            if (! Schema::hasColumn('results', 'sponsorship_start_date')) {
                $table->date('sponsorship_start_date')->nullable();
            }
            if (! Schema::hasColumn('results', 'sponsorship_end_date')) {
                $table->date('sponsorship_end_date')->nullable();
            }
            if (! Schema::hasColumn('results', 'sponsorship_type')) {
                $table->string('sponsorship_type')->nullable();
            }

            // GPA and performance metrics
            if (! Schema::hasColumn('results', 'gpa')) {
                $table->decimal('gpa', 4, 2)->nullable();
            }
            if (! Schema::hasColumn('results', 'overall_gpa')) {
                $table->decimal('overall_gpa', 4, 2)->nullable();
            }
            if (! Schema::hasColumn('results', 'cgpa')) {
                $table->decimal('cgpa', 4, 2)->nullable();
            }
            if (! Schema::hasColumn('results', 'completion_mark')) {
                $table->decimal('completion_mark', 5, 2)->nullable();
            }

            // Summary counts / cached lists
            if (! Schema::hasColumn('results', 'passed_modules')) {
                $table->unsignedInteger('passed_modules')->nullable();
            }
            if (! Schema::hasColumn('results', 'num_failed_modules')) {
                $table->unsignedInteger('num_failed_modules')->nullable();
            }

            if (! Schema::hasColumn('results', 'modules_list')) {
                $table->text('modules_list')->nullable();
            }
            if (! Schema::hasColumn('results', 'modules_passed')) {
                $table->text('modules_passed')->nullable();
            }
            if (! Schema::hasColumn('results', 'failed_modules')) {
                $table->text('failed_modules')->nullable();
            }
            if (! Schema::hasColumn('results', 'repeated_modules')) {
                $table->text('repeated_modules')->nullable();
            }

            // Overall academic outcome
            if (! Schema::hasColumn('results', 'etp_academic_outcome')) {
                $table->string('etp_academic_outcome')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('results', function (Blueprint $table) {
            // Be conservative here – only drop if they exist
            foreach ([
                'sponsorship_start_date',
                'sponsorship_end_date',
                'sponsorship_type',
                'gpa',
                'overall_gpa',
                'cgpa',
                'completion_mark',
                'passed_modules',
                'num_failed_modules',
                'modules_list',
                'modules_passed',
                'failed_modules',
                'repeated_modules',
                'etp_academic_outcome',
            ] as $column) {
                if (Schema::hasColumn('results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
