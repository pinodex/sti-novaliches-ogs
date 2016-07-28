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
			$table->decimal('prelim_presences', 5, 2);
			$table->decimal('midterm_presences', 5, 2);
			$table->decimal('prefinal_presences', 5, 2);
			$table->decimal('final_presences', 5, 2);
			$table->decimal('prelim_absences', 5, 2);
			$table->decimal('midterm_absences', 5, 2);
			$table->decimal('prefinal_absences', 5, 2);
			$table->decimal('final_absences', 5, 2);
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
