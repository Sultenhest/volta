<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WeeklyStatisticsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_user_has_tasks_billed_statistics()
    {
        $user = $this->apiSignIn();
        $project = $user->projects()->create(['title' => 'project 1']);

        $project->addTask([
            'title' => 'This week 1',
            'billed_at' => Carbon::now()
        ]);
        $project->addTask([
            'title' => 'This week 2',
            'billed_at' => Carbon::now()
        ]);
        $task = $project->addTask(['title' => 'Last week 1']);

        $task->update([
            'billed_at' => Carbon::now()->subWeek()
        ]);

        $statistics = $user->statistics()->get('tasks')['billed_at'];

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->format('W')
        ));

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->subWeek()->format('W')
        ));

        $this->assertEquals(2, $statistics->first());
        $this->assertEquals(1, $statistics->last());
    }

    public function test_user_has_tasks_completed_statistics()
    {
        $user = $this->apiSignIn();
        $project = $user->projects()->create(['title' => 'project 1']);

        $project->addTask([
            'title' => 'This week 1',
            'completed_at' => Carbon::now()
        ]);
        $project->addTask([
            'title' => 'This week 2',
            'completed_at' => Carbon::now()
        ]);
        $task = $project->addTask(['title' => 'Last week 1']);

        $task->update([
            'completed_at' => Carbon::now()->subWeek()
        ]);

        $statistics = $user->statistics()->get('tasks')['completed_at'];

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->format('W')
        ));

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->subWeek()->format('W')
        ));

        $this->assertEquals(2, $statistics->first());
        $this->assertEquals(1, $statistics->last());
    }

    public function test_user_has_tasks_created_statistics()
    {
        $user = $this->apiSignIn();
        $project = $user->projects()->create(['title' => 'project 1']);

        $project->addTask(['title' => 'This week 1']);
        $project->addTask(['title' => 'This week 2']);
        $task = $project->addTask(['title' => 'Last week 1']);

        $task->update([
            'created_at' => Carbon::now()->subWeek()
        ]);

        $statistics = $user->statistics()->get('tasks')['created_at'];

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->format('W')
        ));

        $this->assertTrue($statistics->keys()->contains(
            Carbon::now()->subWeek()->format('W')
        ));

        $this->assertEquals(2, $statistics->first());
        $this->assertEquals(1, $statistics->last());
    }
}
