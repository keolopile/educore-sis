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
    Schema::create('result_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('result_id')->constrained()->cascadeOnDelete();
        $table->foreignId('module_id')->constrained()->cascadeOnDelete();

        $table->decimal('mark', 5, 2)->nullable();   // 0–100
        $table->string('grade', 5)->nullable();      // A, B+, etc.
        $table->string('remark', 50)->nullable();    // Pass, Fail, etc.
        $table->boolean('is_supplementary')->default(false);

        $table->timestamps();

        $table->unique(['result_id', 'module_id']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_items');
    }
};
