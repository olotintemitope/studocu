<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Question extends Model
{
	protected $table = 'questions';

	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */
	protected $fillable = [
		'question',
	];

	public function answer(): HasOne
    {
        return $this->hasOne(Answer::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    /**
     * @param $query
     * @param $question
     * @return mixed
     */
    public function scopeOfWhereQuestion($query, $question)
    {
       return $query->where('question', $question);
    }
}