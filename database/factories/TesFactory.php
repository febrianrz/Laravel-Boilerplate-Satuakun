<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Tes;
use Faker\Generator as Faker;

$factory->define(Tes::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
    ];
});
