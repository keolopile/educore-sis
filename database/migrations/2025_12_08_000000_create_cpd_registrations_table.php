<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cpd_registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cpd_session_id')
                ->constrained('cpd_sessions')
                ->cascadeOnDelete();

            // Optional – allow null when user is not logged in
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('full_name');
            $table->string('email');
            $table->string('organisation')->nullable();
            $table->string('role')->nullable();
            $table->text('special_requirements')->nullable();

            $table->string('status')->default('pending_payment'); // e.g. pending_payment, paid, cancelled

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpd_registrations');
    }
};
