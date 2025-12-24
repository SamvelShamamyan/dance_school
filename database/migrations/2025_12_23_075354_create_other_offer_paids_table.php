<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_offer_paids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('other_offer_group_id');
            $table->unsignedBigInteger('student_id');
            $table->boolean('paid_status')->nullable();
            $table->timestamps();

            $table->unique(['other_offer_group_id', 'student_id'],'oop_group_student_unique');

            $table->index('other_offer_group_id', 'other_offer_group_idx');
            $table->foreign('other_offer_group_id')
                ->references('id')
                ->on('other_offer_groups')->cascadeOnDelete();
                

            $table->index('student_id', 'student_idx');
            $table->foreign('student_id')
                ->references('id')
                ->on('students');
                
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_offer_paids');
    }
};
