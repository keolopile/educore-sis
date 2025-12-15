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
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->foreignId('institution_id')->constrained()->cascadeOnDelete();

        $table->string('student_number')->nullable(); // internal number (we can auto-generate later)
        $table->string('national_id', 20)->nullable(); // Omang or birth cert
        $table->string('first_name');
        $table->string('middle_name')->nullable();
        $table->string('last_name');
        $table->date('date_of_birth')->nullable();
        $table->string('gender', 10)->nullable();
        $table->string('nationality')->nullable();

        $table->string('phone')->nullable();
        $table->string('alt_phone')->nullable();
        $table->string('email')->nullable();
        $table->text('address')->nullable();

        // Sponsorship & status
        $table->string('sponsor_type')->default('DTEF'); // DTEF, SELF, COMPANY, etc.
        $table->string('status')->default('Active');     // Active, Graduated, Suspended, Withdrawn

        $table->timestamps();
        $table->softDeletes();

        $table->unique(['institution_id', 'student_number']);
        $table->unique(['institution_id', 'national_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
