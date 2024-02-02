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

$factory->define(Tests\Fixtures\Post::class, function (Faker $faker) {
    return [
        'user_id' => Tests\Fixtures\User::factory(),
        'title' => $faker->word,
        'word_count' => random_int(100, 500),
        'published_at' => now()->subDays(random_int(1, 30)),
    ];
});
