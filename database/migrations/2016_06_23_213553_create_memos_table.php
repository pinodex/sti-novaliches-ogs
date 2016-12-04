<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMemosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('memos', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('admin_id')->nullable();
			$table->integer('faculty_id')->nullable();
			$table->string('subject');
			$table->text('content', 65535);
			$table->timestamps();
			$table->timestamp('opened_at')->nullable();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('memos');
	}

}
