<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeachersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('profile_pic')->nullable();
             $table->unsignedInteger('address_id');
            $table->string('total_exp')->nullable();
            $table->string('total_rel_exp')->nullable();
            $table->string('prefer_time')->nullable();
            $table->string('teach_place')->nullable();
            $table->string('charges')->nullable();
            $table->string('sample_details')->nullable();
            $table->string('sample_link')->nullable();
            $table->string('linkedin_id')->nullable();
            $table->string('resume')->nullable();

            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
             $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers');
    }
}

