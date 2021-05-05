<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Answer;
use App\Models\Option;
use App\Models\Question;
use Faker\Generator as Faker;

$factory->define(Answer::class, function (Faker $faker) {
    return [
        'answer' => $faker->shuffleString(),
        'question_id' => function(array $answers) {
            return Answer::find($answers['answer'])->question_id;
        }
    ];
});
