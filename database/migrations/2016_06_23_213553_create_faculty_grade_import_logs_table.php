<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFacultyGradeImportLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('faculty_grade_import_logs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('faculty_id');
			$table->enum('period', array('PRELIM','MIDTERM','PREFINAL','FINAL'))->nullable();
			$table->dateTime('date');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('faculty_grade_import_logs');
	}

}
