<?php

/** @var Factory $factory */

use App\Models\Option;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Option::class, function (Faker $faker) {
    return [
        'option' => $faker->title,
        'question_id' => random_int(1, 10)
    ];
});
