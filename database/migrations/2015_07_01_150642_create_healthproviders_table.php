<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHealthprovidersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//
		Schema::dropIfExists('healthproviders');

		Schema::create('healthproviders',
			function($table)
			{
				$table->engine ='InnoDB';
				$table->increments('healthprovider_id');
				$table->string('name',200)->nullable()->default(NULL);
				$table->string('address',200)->nullable()->default(NULL);
				$table->string('phone',80)->nullable()->default(NULL);
				$table->string('domain',64)->nullable()->default(NULL);
				$table->string('email',160)->nullable()->default(NULL);
				//$table->text('json')->nullable();  	
				$table->timestamps(); 
//				$table->primary('postal_code');
			});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
		//
		Schema::dropIfExists('healthproviders');
	}

}
