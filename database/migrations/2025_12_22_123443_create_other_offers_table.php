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
        Schema::create('other_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('payments', 12, 2);   
            $table->unsignedBigInteger('school_id');
            $table->timestamps();

            $table->index('school_id', 'school_idx');
            $table->foreign('school_id')->references('id')->on('school_names');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_offers');
    }
};
