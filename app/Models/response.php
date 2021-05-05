<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class response extends Pivot
{
	protected $table = 'responses';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'question_id',
		'answer_id',
	];


}