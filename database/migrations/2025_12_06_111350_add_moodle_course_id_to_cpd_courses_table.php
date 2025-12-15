<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cpd_courses', function (Blueprint $table) {
            $table->unsignedBigInteger('moodle_course_id')->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('cpd_courses', function (Blueprint $table) {
            $table->dropColumn('moodle_course_id');
        });
    }
};
