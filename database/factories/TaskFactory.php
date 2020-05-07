<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Task;
use Faker\Generator as Faker;

$factory->define(Task::class, function (Faker $faker) {
    $project = factory(App\Project::class)->create();
    return [
        'user_id'    => $project->user->id,
        'project_id' => $project,
        'title'      => $faker->sentence(),
    ];
});
