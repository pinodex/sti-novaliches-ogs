<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGradesForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grades', function(Blueprint $table)
        {
            $table->foreign('student_id')->references('id')->on('students')->onDelete('no action')->onUpdate('cascade');
            $table->foreign('importer_id')->references('id')->on('faculties')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
