<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Option extends Model
{
	protected $table = 'options';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'option',
		'question_id',
	];

	public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

}