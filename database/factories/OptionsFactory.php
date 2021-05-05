<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Option;
use App\Models\Question;
use Faker\Generator as Faker;

$factory->define(Option::class, function (Faker $faker) {
    return [
        'option' => $faker->title,
        'question_id' => random_int(1, 10)
    ];
});
