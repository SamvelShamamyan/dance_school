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
        Schema::table('students', function (Blueprint $table) {
            $table->string('phone_1', 9)->after('email');
            $table->string('phone_2', 9)->nullable()->after('phone_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('phone_1', 9)->after('email');
            $table->string('phone_2', 9)->nullable()->after('phone_1');
        });
    }
};
