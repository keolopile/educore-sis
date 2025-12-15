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
    Schema::create('programmes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('institution_id')->constrained()->cascadeOnDelete();

        $table->string('code');               // must match DTEF "institution_program_code"
        $table->string('name');
        $table->string('level')->nullable();  // Certificate, Diploma, Degree
        $table->unsignedTinyInteger('duration_years')->default(3);
        $table->boolean('is_active')->default(true);

        $table->timestamps();
        $table->softDeletes();

        $table->unique(['institution_id', 'code']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
