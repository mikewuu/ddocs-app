<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Checklist;
use App\File;
use App\Recipient;
use App\User;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(Recipient::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'checklist_id' => factory(Checklist::class)->create()->id
    ];
});

$factory->define(Checklist::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->paragraphs(1, true),
        'user_id' => factory(User::class)->create()->id
    ];
});

$factory->define(File::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'description' => $faker->sentences(3, true),
        'user_id' => factory(User::class)->create()->id
    ];
});

$factory->define(\App\FileRequest::class, function (Faker\Generator $faker) {
    return [
        'due' => $faker->dateTimeBetween('now', '+1 year'),
        'checklist_id' => factory(Checklist::class)->create()->id,
        'file_id' => factory(File::class)->create()->id
    ];
});

