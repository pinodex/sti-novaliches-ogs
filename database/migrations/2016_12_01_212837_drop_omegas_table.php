<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropOmegasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('omegas');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('omegas', function(Blueprint $table)
        {
            $table->string('student_id', 11);
            $table->string('subject', 32);
            $table->string('section', 32);
            $table->decimal('prelim_grade', 3, 2)->nullable();
            $table->decimal('midterm_grade', 3, 2)->nullable();
            $table->decimal('prefinal_grade', 3, 2)->nullable();
            $table->decimal('final_grade', 3, 2)->nullable();
            $table->primary(['student_id','subject','section']);
        });
    }
}
