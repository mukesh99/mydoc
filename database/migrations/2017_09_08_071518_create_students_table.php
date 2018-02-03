<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('profile_pic')->nullable();
            $table->string('line2')->nullable();
            $table->string('course_name')->nullable();
            $table->string('learn_place')->nullable();
            $table->string('learn_time')->nullable();
            $table->string('qualification')->nullable();
            $table->string('job_type')->nullable();
            $table->string('level')->nullable();
            $table->string('exp_years')->nullable();
            $table->string('exp_months')->nullable();
            $table->string('exp_days')->nullable();
            $table->string('job_location')->nullable();
            $table->string('resume')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
