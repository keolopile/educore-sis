<?php

// database/migrations/xxxx_add_position_to_cpd_lesson_progress_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cpd_lesson_progress', function (Blueprint $table) {
            $table->unsignedInteger('last_position_seconds')
                  ->default(0)
                  ->after('seconds_watched');
        });
    }

    public function down(): void
    {
        Schema::table('cpd_lesson_progress', function (Blueprint $table) {
            $table->dropColumn('last_position_seconds');
        });
    }
};
