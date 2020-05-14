<?php

namespace Tests\Feature;

use App\Task;
use App\Client;
use App\Project;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TriggerActivityTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_creating_a_client()
    {
        $user = $this->apiSignIn();

        $client = factory(Client::class)->create([
            'user_id' => $user->id
        ]);

        $this->assertCount(1, $user->activity);

        tap($user->activity->last(), function($activity) {
            $this->assertEquals('created_client', $activity->description);
            $this->assertNull($activity->changes);
        });
    }

    public function test_updating_a_client()
    {
        $user = $this->apiSignIn();

        $client = factory(Client::class)->create([
            'user_id' => $user->id
        ]);

        $originalTitle = $client->name;

        $client->update(['name' => 'New Name']);

        $this->assertCount(2, $user->activity);
        $this->assertEquals('created_client', $user->activity->first()->description);

        tap($client->activity->last(), function($activity) use ($originalTitle) {
            $this->assertEquals('updated_client', $activity->description);
            $expected = [
                'before' => ['name' => $originalTitle],
                'after'  => ['name' => 'New Name']
            ];

            $this->assertEquals($expected, $activity->changes);
        });
    }

    public function test_deleting_a_client()
    {
        $user = $this->apiSignIn();

        $client = factory(Client::class)->create([
            'user_id' => $user->id
        ]);

        $client->delete();

        $this->assertCount(2, $user->activity);
        $this->assertEquals('deleted_client', $user->activity->last()->description);
    }

    public function test_creating_a_project()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $this->assertCount(1, $user->activity);

        tap($user->activity->last(), function($activity) {
            $this->assertEquals('created_project', $activity->description);
            $this->assertNull($activity->changes);
        });
    }

    public function test_updating_a_project()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $originalTitle = $project->title;

        $project->update(['title' => 'New Title']);

        $this->assertCount(2, $user->activity);
        $this->assertEquals('created_project', $user->activity->first()->description);

        tap($project->activity->last(), function($activity) use ($originalTitle) {
            $this->assertEquals('updated_project', $activity->description);
            $expected = [
                'before' => ['title' => $originalTitle],
                'after'  => ['title' => 'New Title']
            ];

            $this->assertEquals($expected, $activity->changes);
        });
    }

    public function test_deleting_a_project()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $project->delete();

        $this->assertCount(2, $user->activity);
        $this->assertEquals('deleted_project', $user->activity->last()->description);
    }

    public function test_creating_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);

        $this->assertCount(2, $user->activity);

        tap($user->activity->last(), function($activity) {
            $this->assertEquals('created_task', $activity->description);
            $this->assertInstanceOf(Task::class, $activity->subject);
            $this->assertEquals('task 1', $activity->subject->title);
        });
    }

    public function test_updating_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'Original Task Title']);

        $this->assertCount(2, $user->activity);
        $this->assertEquals('created_project', $user->activity->first()->description);
        $this->assertEquals('created_task', $user->activity->last()->description);

        $task->update(['title' => 'New Task Title']);

        $this->assertCount(3, $user->refresh()->activity);

        tap($user->activity->last(), function($activity) {
            $this->assertEquals('updated_task', $activity->description);
            $expected = [
                'before' => ['title' => 'Original Task Title'],
                'after'  => ['title' => 'New Task Title']
            ];

            $this->assertEquals($expected, $activity->changes);
        });
    }

    public function test_completing_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed');

        $this->assertCount(3, $user->activity);
        $this->assertEquals('completed_task', $user->activity->last()->description);
    }

    public function test_incompleting_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed');

        $this->assertCount(3, $user->activity);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed');

        $user->refresh();

        $this->assertCount(4, $user->activity);
        $this->assertEquals('incompleted_task', $user->activity->last()->description);
    }

    public function test_billing_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed');

        $this->assertCount(3, $user->activity);
        $this->assertEquals('billed_task', $user->activity->last()->description);
    }

    public function test_unbillling_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed');

        $this->assertCount(3, $user->activity);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed');

        $user->refresh();

        $this->assertCount(4, $user->activity);
        $this->assertEquals('unbilled_task', $user->activity->last()->description);
    }

    public function test_deleting_a_task()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);

        $task->delete();

        $this->assertCount(3, $user->activity);
        $this->assertEquals('deleted_task', $user->activity->last()->description);
    }
}
