<?php

// database/migrations/2025_01_01_000030_create_enrolments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrolments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('cpd_session_id')
                ->constrained('cpd_sessions')
                ->cascadeOnDelete();

            // "pending", "active", "cancelled", "completed"
            $table->string('enrolment_status', 20)->default('pending');

            // "pending", "paid", "failed", "waived"
            $table->string('payment_status', 20)->default('pending');

            // optional: organisation details
            $table->string('organisation_name')->nullable();
            $table->string('position_title')->nullable();

            // store Moodle user + enrolment reference if needed
            $table->unsignedBigInteger('moodle_user_id')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'cpd_session_id'], 'enrolments_user_session_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrolments');
    }
};
