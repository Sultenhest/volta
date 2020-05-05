<?php

namespace Tests\Unit;

use App\User;
use App\Project;
use App\Activity;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_a_user()
    {   
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn($project->user);

        $this->assertDatabaseHas('activities', [
            'user_id'      => $user->id,
            'subject_type' => 'App\Project',
            'subject_id'   => $project->id,
            'description'  => 'created_project',
        ]);

        $activity = Activity::first();

        $this->assertEquals($activity->user_id, $user->id);
    }
}
