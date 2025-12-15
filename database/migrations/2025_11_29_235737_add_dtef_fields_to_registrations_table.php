<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // DTEF-aligned fields for the register API

            $table->string('campus')->nullable();                // DTEF "campus"
            $table->date('sem_start_date')->nullable();          // sem_start_date
            $table->date('sem_end_date')->nullable();            // sem_end_date

            $table->date('sponsor_start_date')->nullable();      // Sponsor_start_date
            $table->date('sponsor_end_date')->nullable();        // Sponsor_end_date
            $table->string('sponsor_cat')->nullable();           // OVC/SEN/RAC/TA/M etc.
            $table->string('sponsor_type')->nullable();          // NEW STUDENT / PROGRESSION / ...

            $table->string('register_comparison')->nullable();   // for DTEF register_comparison
            $table->string('modules_repeated')->nullable();      // comma list for Modules_repeated
            $table->text('modules_string')->nullable();          // cached "ACC4014, ACC5004, ..."
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'campus',
                'sem_start_date',
                'sem_end_date',
                'sponsor_start_date',
                'sponsor_end_date',
                'sponsor_cat',
                'sponsor_type',
                'register_comparison',
                'modules_repeated',
                'modules_string',
            ]);
        });
    }
};
