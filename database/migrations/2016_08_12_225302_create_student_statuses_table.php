<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_statuses', function (Blueprint $table) {
            $table->string('student_id', 11)->primary();
            $table->boolean('prelim')->default(false);
            $table->boolean('midterm')->default(false);
            $table->boolean('prefinal')->default(false);
            $table->boolean('final')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('student_statuses');
    }
}
