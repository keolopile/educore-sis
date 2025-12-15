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
    Schema::create('modules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('programme_id')->constrained()->cascadeOnDelete();

        $table->string('code'); // e.g. HRM511
        $table->string('name');
        $table->unsignedTinyInteger('level_number')->nullable(); // year level
        $table->unsignedTinyInteger('credits')->nullable();
        $table->boolean('is_active')->default(true);

        $table->timestamps();
        $table->softDeletes();

        $table->unique(['programme_id', 'code']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
