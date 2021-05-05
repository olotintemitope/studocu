<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Answer;
use App\Models\Question;
use App\Models\Response;
use Faker\Generator as Faker;

$factory->define(Response::class, function (Faker $faker) {
    return [
        'answer_id' => $faker->randomNumber(),
        'question_id' => function($question) {
            return Question::find($question['id'])->id;
        }
    ];
});
