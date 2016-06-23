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
			$table->integer('prelim_attendance_hours');
			$table->integer('midterm_attendance_hours');
			$table->integer('prefinal_attendance_hours');
			$table->integer('final_attendance_hours');
			$table->integer('prelim_absent_hours');
			$table->integer('midterm_absent_hours');
			$table->integer('prefinal_absent_hours');
			$table->integer('final_absent_hours');
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
