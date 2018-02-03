<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_coupons', function (Blueprint $table) {
            $table->increments('id');

           $table->unsignedInteger('teacher_course_id');
            $table->string('discount');
            $table->string('code');
            $table->dateTime('expiry_date');
            $table->boolean('used')->default(false);
            $table->timestamps();
            $table->foreign('teacher_course_id')->references('id')->on('teacher_courses')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_coupons');
    }
}
