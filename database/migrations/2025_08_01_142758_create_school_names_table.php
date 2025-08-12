<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolNamesTable extends Migration
{
    public function up()
    {
        Schema::create('school_names', function (Blueprint $table) {
            $table->id(); 
            $table->string('name', 25)->nullable();
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('school_names');
    }
}
