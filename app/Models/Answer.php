<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
	protected $table = 'answers';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'answer',
		'question_id',
	];

	public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function questionAsked(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'responses');
    }
}