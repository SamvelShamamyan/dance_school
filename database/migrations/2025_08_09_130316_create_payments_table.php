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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable(); 
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); 

            $table->decimal('amount', 12, 2);         
            $table->char('currency', 3)->default('AMD');    
            $table->dateTime('paid_at');     
            
            $table->enum('method', ['cash','card','online'])->default('cash');
            $table->enum('status', ['paid','pending','failed','refunded'])->default('paid');
            $table->string('comment', 255)->nullable();

            $table->timestamps();

            $table->index(['school_id', 'group_id', 'status', 'paid_at'], 'payments_scope_idx');
            $table->index('student_id', 'payments_student_idx');
            $table->index('method', 'payments_method_idx');
            $table->index('paid_at', 'payments_paid_at_idx');

            $table->foreign('school_id')->references('id')->on('school_names')->nullOnDelete();
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
