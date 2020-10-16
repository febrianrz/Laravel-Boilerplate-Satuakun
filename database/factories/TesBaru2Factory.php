<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\TesBaru2;
use Faker\Generator as Faker;

$factory->define(TesBaru2::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
