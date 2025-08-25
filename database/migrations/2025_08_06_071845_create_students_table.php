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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('father_name');
            $table->string('email', 191)->unique();
            $table->string('address'); 
            $table->date('birth_date');
            $table->string('soc_number');
            $table->date('created_date');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->date('group_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id', 'school_name_idx');
            $table->foreign('school_id')->references('id')->on('school_names')->nullOnDelete();

            $table->index('group_id', 'school_group_idx');
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
