<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGradeImportLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('grade_import_logs', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('faculty_id');
			$table->enum('period', ['PRELIM','MIDTERM','PREFINAL','FINAL'])->nullable();
			$table->string('subject', 32);
			$table->string('section', 32);
			$table->dateTime('date');
			$table->boolean('is_valid')->default(true);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('grade_import_logs');
	}

}
