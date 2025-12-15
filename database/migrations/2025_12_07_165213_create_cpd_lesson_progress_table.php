<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpd_lesson_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpd_lesson_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('seconds_watched')->default(0);
            $table->boolean('completed')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'cpd_lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_lesson_progress');
    }
};
