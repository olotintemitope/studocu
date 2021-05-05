<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponsesTable extends Migration
{
	/**
	 * Run the migrations.
	 * 
	 * @return void
	 */
	public function up()
	{
		Schema::create('responses', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('question_id');
			$table->integer('answer_id');
			$table->timestamps();

            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('answer_id')->references('id')->on('answers');

		});
	}

	/**
	 * Reverse the migrations.
	 * 
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('responses');
	}

}