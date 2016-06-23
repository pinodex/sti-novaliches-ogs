<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFacultiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('faculties', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('department_id')->nullable();
			$table->string('username');
			$table->string('password', 60);
			$table->string('last_name');
			$table->string('first_name');
			$table->string('middle_name');
			$table->timestamps();
			$table->dateTime('last_login_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('faculties');
	}

}
