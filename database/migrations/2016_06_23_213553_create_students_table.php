<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStudentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('students', function(Blueprint $table)
		{
			$table->string('id', 11)->primary();
			$table->string('last_name');
			$table->string('first_name');
			$table->string('middle_name');
			$table->string('course', 32);
			$table->string('section', 32);
			$table->string('mobile_number', 16)->nullable();
			$table->string('landline', 16)->nullable();
			$table->string('email_address')->nullable();
			$table->text('address', 65535)->nullable();
			$table->string('guardian_name')->nullable();
			$table->string('guardian_contact_number', 16)->nullable();
			$table->string('other_info')->nullable();
			$table->text('remarks', 65535)->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('students');
	}

}
