<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCronLogsTable extends Migration {

/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::dropIfExists('cron_logs');

		Schema::create('cron_logs',
			function($table)
			{
				$table->engine ='InnoDB';
				$table->increments('cron_id');
				$table->string('name',130)->nullable();
				$table->unsignedInteger('operations')->default(0);
				$table->string('operation',130)->default('');
				$table->text('results'); //->default('');
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
		Schema::dropIfExists('cron_logs');
	}

}
