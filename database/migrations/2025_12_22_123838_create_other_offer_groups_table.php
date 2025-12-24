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
        Schema::create('other_offer_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('other_offer_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->timestamps();

            $table->index('other_offer_id', 'other_offer_idx');
            $table->foreign('other_offer_id')->references('id')->on('other_offers')->cascadeOnDelete();

            $table->index('group_id', 'school_group_idx');
            $table->foreign('group_id')->references('id')->on('groups');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_offer_groups');
    }
};
