<?php

// database/migrations/2025_01_01_000020_create_cpd_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cpd_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cpd_course_id')
                ->constrained('cpd_courses')
                ->cascadeOnDelete();

            // Dates for this run
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // "online", "face_to_face", "hybrid"
            $table->string('delivery_mode', 20)->default('online');

            // e.g. "Gaborone Campus", "Online (Zoom + Moodle)"
            $table->string('location')->nullable();

            // Pricing for this run (can override default)
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('BWP');

            // Capacity & registration control
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('seats_taken')->default(0);

            // Link to Moodle course if this session is online
            $table->unsignedBigInteger('moodle_course_id')->nullable();

            // "draft", "open", "closed", "completed", "cancelled"
            $table->string('status', 20)->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_sessions');
    }
};
