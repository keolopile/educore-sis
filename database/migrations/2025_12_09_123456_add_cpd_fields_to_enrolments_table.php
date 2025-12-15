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
        Schema::table('enrolments', function (Blueprint $table) {
            // 🔹 Personal details
            $table->string('id_number')->nullable()->after('position_title');
            $table->string('gender', 20)->nullable()->after('id_number');

            // 🔹 Contact details (personal)
            $table->string('phone')->nullable()->after('gender');
            $table->string('address')->nullable()->after('phone');

            // 🔹 Employment details
            $table->string('employer')->nullable()->after('address');
            $table->string('designation')->nullable()->after('employer');
            $table->string('department')->nullable()->after('designation');
            $table->string('work_phone')->nullable()->after('department');
            $table->string('work_email')->nullable()->after('work_phone');

            // 🔹 Sponsorship info
            $table->enum('sponsorship_type', ['self', 'employer'])
                  ->nullable()
                  ->after('work_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrolments', function (Blueprint $table) {
            $table->dropColumn([
                'id_number',
                'gender',
                'phone',
                'address',
                'employer',
                'designation',
                'department',
                'work_phone',
                'work_email',
                'sponsorship_type',
            ]);
        });
    }
};
