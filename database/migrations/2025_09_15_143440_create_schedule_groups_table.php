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
        Schema::create('schedule_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('note')->nullable(); 
            $table->enum('color', ['blue', 'green', 'purple', 'orange'])->default('blue'); 
            $table->unsignedTinyInteger('week_day'); 
            $table->time('start_time'); 
            $table->time('end_time');  

            $table->unsignedBigInteger('school_id')->nullable(); 
            $table->unsignedBigInteger('group_id')->nullable(); 
            $table->unsignedBigInteger('room_id')->nullable();    
            
            $table->date('active_from')->nullable();
            $table->date('active_to')->nullable();   
            $table->boolean('is_active')->default(true); 
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('school_names')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_groups');
    }
};
