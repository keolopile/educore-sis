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
    Schema::create('admissions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
        $table->foreignId('student_id')->constrained()->cascadeOnDelete();
        $table->foreignId('programme_id')->constrained()->cascadeOnDelete();

        $table->date('commencement_date')->nullable();
        $table->date('expected_completion_date')->nullable();
        $table->unsignedTinyInteger('level_of_entry')->default(1); // Year 1, etc.
        $table->decimal('programme_cost', 12, 2)->nullable();

        $table->string('admission_status')->default('Approved'); // Pending, Approved, Rejected

        // DTEF status tracking (we'll use this when we add the API calls)
        $table->string('dtef_status')->default('Not Sent'); // Not Sent, Sent, Accepted, Rejected, Error
        $table->text('last_dtef_response')->nullable();
        $table->timestamp('last_dtef_at')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
        $table->softDeletes();

        // one admission per student + programme (for now)
        $table->unique(['student_id', 'programme_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
