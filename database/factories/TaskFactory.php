<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'project_id' => factory(App\Project::class)->create(),
        'title'      => $faker->sentence(),
    ];
});
