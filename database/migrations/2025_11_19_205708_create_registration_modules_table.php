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
    Schema::create('registration_modules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
        $table->foreignId('module_id')->constrained()->cascadeOnDelete();
        $table->boolean('is_repeated')->default(false);
        $table->timestamps();

        $table->unique(['registration_id', 'module_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_modules');
    }
};
