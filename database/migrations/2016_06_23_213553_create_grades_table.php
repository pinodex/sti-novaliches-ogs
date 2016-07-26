<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGradesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('grades', function(Blueprint $table)
		{
			$table->string('student_id', 11);
			$table->integer('importer_id')->nullable();
			$table->string('subject', 32);
			$table->string('section', 32);
			$table->integer('prelim_grade')->nullable();
			$table->integer('midterm_grade')->nullable();
			$table->integer('prefinal_grade')->nullable();
			$table->integer('final_grade')->nullable();
			$table->integer('prelim_presences');
			$table->integer('midterm_presences');
			$table->integer('prefinal_presences');
			$table->integer('final_presences');
			$table->integer('prelim_absences');
			$table->integer('midterm_absences');
			$table->integer('prefinal_absences');
			$table->integer('final_absences');
			$table->primary(['student_id','subject','section']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('grades');
	}

}
