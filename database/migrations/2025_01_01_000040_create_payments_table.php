<?php

// database/migrations/2025_01_01_000040_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('enrolment_id')
                ->constrained('enrolments')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('BWP');

            // e.g. "card", "orange_money", "eft"
            $table->string('method', 50)->nullable();

            // reference from your system
            $table->string('local_reference')->unique();

            // reference from the gateway / PSP
            $table->string('gateway_reference')->nullable();

            // "pending", "success", "failed", "refunded"
            $table->string('status', 20)->default('pending');

            // keep entire API callback payload for traceability
            $table->json('gateway_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
