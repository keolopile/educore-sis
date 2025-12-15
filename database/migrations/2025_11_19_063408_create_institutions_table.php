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
    Schema::create('institutions', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('short_code')->unique(); // BUAN, IDM, KITSO, etc.
        $table->string('logo_path')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->string('address_line1')->nullable();
        $table->string('address_line2')->nullable();
        $table->string('city')->nullable();
        $table->string('country')->default('Botswana');

        // DTEF integration config
        $table->boolean('dtef_enabled')->default(false);
        $table->string('dtef_environment')->default('test'); // test | live
        $table->string('dtef_username')->nullable();
        $table->string('dtef_password')->nullable();

        $table->timestamps();
        $table->softDeletes();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
