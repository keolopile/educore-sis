<?php 
// database/migrations/xxxx_create_cpd_lesson_quizzes_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cpd_lesson_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpd_lesson_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('time_offset_seconds'); // when to pop up
            $table->string('title');
            $table->text('question');
            $table->string('type')->default('mcq'); // 'mcq', 'truefalse', etc.
            $table->boolean('required_to_continue')->default(true);
            $table->timestamps();
        });

        Schema::create('cpd_lesson_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpd_lesson_quiz_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('label');       // e.g. "A"
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('cpd_lesson_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpd_lesson_quiz_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->boolean('is_correct');
            $table->unsignedInteger('score')->default(0);
            $table->json('answers')->nullable(); // store selected options
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_lesson_quiz_attempts');
        Schema::dropIfExists('cpd_lesson_quiz_options');
        Schema::dropIfExists('cpd_lesson_quizzes');
    }
};
