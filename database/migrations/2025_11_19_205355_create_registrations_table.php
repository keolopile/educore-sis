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
    Schema::create('registrations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
        $table->foreignId('student_id')->constrained()->cascadeOnDelete();
        $table->foreignId('programme_id')->constrained()->cascadeOnDelete();
        $table->unsignedTinyInteger('study_year');     // 1,2,3...
        $table->unsignedTinyInteger('study_semester'); // 1,2...
        $table->boolean('accommodation')->default(false);
        $table->date('registration_date')->nullable();
        $table->string('registration_status')->default('Active'); // Active, Cancelled

        $table->string('dtef_status')->default('Not Sent');
        $table->text('last_dtef_response')->nullable();
        $table->timestamp('last_dtef_at')->nullable();

        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps();
        $table->softDeletes();

        $table->unique(['student_id', 'programme_id', 'study_year', 'study_semester'], 'uniq_reg_per_sem');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
