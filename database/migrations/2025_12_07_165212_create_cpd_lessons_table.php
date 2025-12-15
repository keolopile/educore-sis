<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpd_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpd_course_module_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('video_provider', ['file', 'vimeo', 'youtube', 'url'])->default('url');
            $table->string('video_reference')->nullable();

            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_preview')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_lessons');
    }
};

