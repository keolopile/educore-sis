<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('results', function (Blueprint $table) {
        $table->id();
        $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
        $table->foreignId('student_id')->constrained()->cascadeOnDelete();
        $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
        $table->foreignId('registration_id')->nullable()->constrained()->nullOnDelete();

        $table->unsignedTinyInteger('study_year');       // 1,2,3...
        $table->unsignedTinyInteger('study_semester');   // 1,2...

        $table->string('academic_year')->nullable();     // e.g. 2025/2026
        $table->string('exam_session')->default('MAIN'); // MAIN, SUPP, REMARK, etc.

        $table->string('overall_status')->default('Pending'); // Pending, Pass, Fail, Incomplete
        $table->decimal('gpa', 4, 2)->nullable();
        $table->text('remarks')->nullable();

        // DTEF tracking
        $table->string('dtef_status')->default('Not Sent');
        $table->text('last_dtef_response')->nullable();
        $table->timestamp('last_dtef_at')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
        $table->softDeletes();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
