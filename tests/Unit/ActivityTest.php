<?php

namespace Tests\Unit;

use App\User;
use App\Project;

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

        $this->assertEquals($user->id, $project->activity->first()->user->id);
    }
}
