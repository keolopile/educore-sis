<?php
// database/migrations/2025_01_01_000000_create_cpd_domains_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cpd_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g. "Leadership", "ICT"
            $table->string('slug')->unique(); // e.g. "leadership", "ict"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_domains');
    }
};
