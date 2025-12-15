<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('result_items', function (Blueprint $table) {
            // DTEF expects a compact result per module (p/s/f)
            // p = pass, s = supplementary/resit, f = fail
            $table->char('result_flag', 1)->nullable()->after('grade');

            // Whether this module is a repeat attempt during this term
            $table->boolean('is_repeat')->default(false)->after('result_flag');
        });
    }

    public function down(): void
    {
        Schema::table('result_items', function (Blueprint $table) {
            $table->dropColumn(['result_flag', 'is_repeat']);
        });
    }
};
