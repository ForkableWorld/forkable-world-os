<?php

use Faker\Generator as Faker;
/** @var \Illuminate\Database\Eloquent\Factory $factory */
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Tests\Fixtures\Role::class, function (Faker $faker) {
    return [
        'created_by_id' => Tests\Fixtures\User::factory(),
        'name' => $faker->name,
    ];
});
