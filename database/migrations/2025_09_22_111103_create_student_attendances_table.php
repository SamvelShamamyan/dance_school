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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_group_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->boolean('checked_status')->default(false);
            $table->dateTime('inspection_date')->nullable();
            $table->timestamps();

            $table->foreign('schedule_group_id')->references('id')->on('schedule_groups')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            
            $table->foreign('schedule_group_id')->references('id')->on('schedule_groups')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
