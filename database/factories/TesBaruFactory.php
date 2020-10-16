<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\TesBaru;
use Faker\Generator as Faker;

$factory->define(TesBaru::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
