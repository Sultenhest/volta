<?php

namespace Tests\Unit;

use App\User;
use App\Project;
use App\Activity;

use Carbon\Carbon;

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

    public function test_it_fetches_a_feed_for_any_user()
    {
        $user = $this->apiSignIn();

        $user->projects()->create(['title' => 'project 1']);
        $user->projects()->create(['title' => 'project 2']);

        auth()->user()->activity()->first()->update([
            'created_at' => Carbon::now()->subWeek()
        ]);

        $feed = Activity::feed();

        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->format('Y-m-d')
        ));

        $this->assertTrue($feed->keys()->contains(
            Carbon::now()->subWeek()->format('Y-m-d')
        ));
    }
}
