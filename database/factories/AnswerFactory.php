<?php

/** @var Factory $factory */

use App\Model;
use App\Models\Answer;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Answer::class, function (Faker $faker) {
    return [
        'answer' => $faker->title,
        'question_id' => random_int(1, 10)
    ];
});
