<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {
            // 🔹 Only add columns if they don't already exist

            if (! Schema::hasColumn('enrolments', 'id_number')) {
                $table->string('id_number')->nullable()->after('position_title');
            }

            if (! Schema::hasColumn('enrolments', 'gender')) {
                // use string for max compatibility across drivers
                $table->string('gender', 10)->nullable()->after('id_number');
            }

            if (! Schema::hasColumn('enrolments', 'phone')) {
                $table->string('phone', 50)->nullable()->after('gender');
            }

            if (! Schema::hasColumn('enrolments', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('enrolments', 'employer')) {
                $table->string('employer')->nullable()->after('address');
            }

            if (! Schema::hasColumn('enrolments', 'designation')) {
                $table->string('designation')->nullable()->after('employer');
            }

            if (! Schema::hasColumn('enrolments', 'department')) {
                $table->string('department')->nullable()->after('designation');
            }

            if (! Schema::hasColumn('enrolments', 'work_phone')) {
                $table->string('work_phone', 50)->nullable()->after('department');
            }

            if (! Schema::hasColumn('enrolments', 'work_email')) {
                $table->string('work_email')->nullable()->after('work_phone');
            }

            if (! Schema::hasColumn('enrolments', 'sponsorship_type')) {
                // 'self' or 'employer'
                $table->string('sponsorship_type', 20)->nullable()->after('work_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {
            // 🔹 Drop columns only if they exist, to avoid issues when rolling back

            if (Schema::hasColumn('enrolments', 'sponsorship_type')) {
                $table->dropColumn('sponsorship_type');
            }
            if (Schema::hasColumn('enrolments', 'work_email')) {
                $table->dropColumn('work_email');
            }
            if (Schema::hasColumn('enrolments', 'work_phone')) {
                $table->dropColumn('work_phone');
            }
            if (Schema::hasColumn('enrolments', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('enrolments', 'designation')) {
                $table->dropColumn('designation');
            }
            if (Schema::hasColumn('enrolments', 'employer')) {
                $table->dropColumn('employer');
            }
            if (Schema::hasColumn('enrolments', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('enrolments', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('enrolments', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('enrolments', 'id_number')) {
                $table->dropColumn('id_number');
            }
        });
    }
};
