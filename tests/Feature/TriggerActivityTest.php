<?php

namespace Tests\Feature;

use App\Task;
use App\Project;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TriggerActivityTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_creating_a_project()
    {
        $project = factory(Project::class)->create();

        $this->assertCount(1, $project->activity);

        tap($project->activity->last(), function($activity) {
            $this->assertEquals('created_project', $activity->description);
            $this->assertNull($activity->changes);
        });
    }

    public function test_updating_a_project()
    {
        $project = factory(Project::class)->create();
        $originalTitle = $project->title;

        $project->update(['title' => 'New Title']);

        $this->assertCount(2, $project->activity);
        $this->assertEquals('created_project', $project->activity->first()->description);

        tap($project->activity->last(), function($activity) use ($originalTitle) {
            $this->assertEquals('updated_project', $activity->description);
            $expected = [
                'before' => ['title' => $originalTitle],
                'after'  => ['title' => 'New Title']
            ];

            $this->assertEquals($expected, $activity->changes);
        });
    }

    public function test_creating_a_task()
    {
        $project = factory(Project::class)->create();

        $task = $project->addTask(['title' => 'task 1']);

        $this->assertCount(2, $project->activity);

        tap($project->activity->last(), function($activity) {
            $this->assertEquals('created_task', $activity->description);
            $this->assertInstanceOf(Task::class, $activity->subject);
            $this->assertEquals('task 1', $activity->subject->title);
        });
    }

    public function test_completing_a_task()
    {
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn($project->user);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed');

        $this->assertCount(4, $project->activity);
        $this->assertEquals('completed_task', $project->activity->last()->description);
    }

    public function test_incompleting_a_task()
    {
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn($project->user);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed');

        $this->assertCount(4, $project->activity);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed');

        $project->refresh();

        $this->assertCount(6, $project->activity);
        $this->assertEquals('incompleted_task', $project->activity->last()->description);
    }

    public function test_billing_a_task()
    {
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn($project->user);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed');

        $this->assertCount(4, $project->activity);
        $this->assertEquals('billed_task', $project->activity->last()->description);
    }

    public function test_unbillling_a_task()
    {
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn($project->user);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed');

        $this->assertCount(4, $project->activity);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed');

        $project->refresh();

        $this->assertCount(6, $project->activity);
        $this->assertEquals('unbilled_task', $project->activity->last()->description);
    }

    public function test_deleting_a_task()
    {
        $project = factory(Project::class)->create();

        $task = $project->addTask(['title' => 'task 1']);

        $task->delete();

        $this->assertCount(3, $project->activity);
        $this->assertEquals('deleted_task', $project->activity->last()->description);
    }
}
