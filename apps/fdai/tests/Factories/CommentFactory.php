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

$factory->define(Tests\Fixtures\Comment::class, function (Faker $faker) {
    return [
        'commentable_id' => Tests\Fixtures\Post::factory(),
        'commentable_type' => Tests\Fixtures\Post::class,
        'author_id' => Tests\Fixtures\User::factory(),
        'body' => $faker->word,
    ];
});
