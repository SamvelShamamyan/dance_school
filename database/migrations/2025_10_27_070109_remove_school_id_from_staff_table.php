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
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex('school_name_idx');
            $table->dropColumn('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable();
            $table->index('school_id', 'school_name_idx');
            $table->foreign('school_id')
                ->references('id')
                ->on('school_names')
                ->onDelete('set null');
        });
    }
};
