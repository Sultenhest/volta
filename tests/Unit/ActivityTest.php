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

    public function test_it_fetches_weekly_statistics()
    {
        $user = $this->apiSignIn();

        $user->projects()->create(['title' => 'project 1']);
        $project = $user->projects()->create(['title' => 'project 2']);
        $project->addTask(['title' => 'task 1']);
        $project->addTask(['title' => 'task 2']);
        $project->addTask(['title' => 'task 3']);
        $user->projects()->create(['title' => 'project 3']);

        $user->activity()->first()->update([
            'created_at' => Carbon::now()->subWeek()
        ]);

        $this->assertCount(6, $user->activity);

        $statistics = Activity::statistics();

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->format('W')
        ));

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->subWeek()->format('W')
        ));

        $this->assertCount(2, $statistics->first());
        $this->assertCount(1, $statistics->last());
        $this->assertTrue(
            count(array_intersect(
                $statistics->first()->keys()->toArray(),
                ['created_project', 'created_task']
            )) == count(['created_project', 'created_task'])
        );
        $this->assertTrue(in_array(
            'created_project',
            $statistics->last()->keys()->toArray()
        ));
    }
}
