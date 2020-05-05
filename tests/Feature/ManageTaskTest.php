<?php

namespace Tests\Feature;

use App\Task;
use App\Project;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageTaskTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_guests_cannot_manage_tasks()
    {
        $task = factory(Task::class)->create();

        $this->get('api/tasks')->assertRedirect('login');
        $this->post($task->project->path() . '/tasks', $task->toArray())->assertRedirect('login');
        $this->get($task->path())->assertRedirect('login');
        $this->patch($task->path())->assertRedirect('login');
        $this->delete($task->path())->assertRedirect('login');
        $this->patch($task->path() . '/restore')->assertRedirect('login');
        $this->delete($task->path() . '/forcedelete')->assertRedirect('login');
    }

    public function test_a_user_can_get_a_single_task()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $task = $project->tasks()->create(['title' => 'task 1']);

        $this->assertCount(1, $user->projects);
        $this->assertCount(1, $user->tasks);

        $response = $this->actingAs($user)->getJson($user->tasks->first()->path())
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'task 1'
            ]);
    }

    public function test_a_user_can_get_all_of_their_tasks()
    {        
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $project->tasks()->create(['title' => 'task 1']);
        $project->tasks()->create(['title' => 'task 2']);
        $project->tasks()->create(['title' => 'task 2']);

        $this->assertCount(3, $user->tasks);

        $response = $this->actingAs($user)->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonFragment([
                'title' => 'project 1'
            ]);
    }

    public function test_a_task_requires_a_title()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $response = $this->actingAs($user)
            ->postJson($project->path() . '/tasks', ['title' => ''])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['title']);
    }

    public function test_a_task_hours_spent_has_to_be_atleast_0()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $response = $this->actingAs($user)
            ->postJson($project->path() . '/tasks', [
                'title'       => 'Task title',
                'hours_spent' => -1
            ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['hours_spent']);
    }

    public function test_a_task_minutes_spent_has_to_be_atleast_0()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $response = $this->actingAs($user)
            ->postJson($project->path() . '/tasks', [
                'title'         => $this->faker->sentence(),
                'minutes_spent' => -1
            ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['minutes_spent']);
    }

    public function test_a_task_minutes_spent_has_to_be_below_60()
    {
        
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $response = $this->actingAs($user)
            ->postJson($project->path() . '/tasks', [
                'title'         => $this->faker->sentence(),
                'minutes_spent' => 60
            ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['minutes_spent']);
    }

    public function test_a_user_can_add_tasks_to_their_project()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'Project Title']);

        $response = $this->postJson($project->path() . '/tasks/',
            $attributes = [
                'title' => $this->faker->sentence()
            ])
            ->assertCreated()
            ->assertJson([
                'message' => 'Task was successfully created.',
            ]);

        $this->assertDatabaseHas('tasks', $attributes);
    }

    public function test_a_user_cannot_add_tasks_to_another_users_project()
    {
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn();

        $response = $this->actingAs($user)
            ->postJson($project->path() . '/tasks/', [
                'project_id' => $project->id,
                'title'      => 'Task Title'
            ]);

        $response->assertForbidden()->assertJson([
            'message' => 'This action is unauthorized.'
        ]);
    }

    public function test_a_user_can_update_their_tasks()
    {
        $task = factory(Task::class)->create();

        $user = $this->apiSignIn($task->project->user);

        $response = $this->actingAs($user)
            ->patchJson($task->path(), $attributes = [
                'title' => 'New title',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully updated.',
            ]);

        $this->assertDatabaseHas('tasks', $attributes);
    }

    public function test_a_user_can_complete_a_task()
    {
        $task = factory(Task::class)->create();

        $user = $this->apiSignIn($task->project->user);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed')
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully marked as complete!',
            ]);
    }

    public function test_a_user_can_incomplete_a_task()
    {
        $task = factory(Task::class)->create();

        $task->complete();

        $user = $this->apiSignIn($task->project->user);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/completed')
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully marked as incomplete!',
            ]);
    }

    public function test_a_user_can_mark_a_task_as_billed()
    {
        $task = factory(Task::class)->create();

        $user = $this->apiSignIn($task->project->user);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed')
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully marked as billed!',
            ]);
    }

    public function test_a_user_can_mark_a_task_as_unbilled()
    {
        $task = factory(Task::class)->create();

        $task->billed();

        $user = $this->apiSignIn($task->project->user);

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/billed')
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully marked as unbilled!',
            ]);
    }

    public function test_a_user_can_soft_delete_their_tasks()
    {
        $attributes = ['title' => 'Task Title'];

        $task = factory(Task::class)->create($attributes);

        $user = $this->apiSignIn($task->project->user);

        $response = $this->actingAs($user)
            ->deleteJson($task->path())
            ->assertNoContent();

        $this->assertSoftDeleted('tasks', $attributes);
    }

    public function test_a_user_can_restore_their_soft_deleted_tasks()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project']);

        $attributes = ['title' => 'Task Title'];

        $task = $project->tasks()->create($attributes);

        $task->delete();

        $response = $this->actingAs($user)
            ->patchJson($task->path() . '/restore')
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully restored.',
            ]);

        $this->assertDatabaseHas('tasks', $attributes);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_task()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project']);

        $attributes = ['title' => 'Task Title'];

        $task = $project->tasks()->create($attributes);

        $task->delete();

        $this->actingAs($user)
            ->deleteJson($task->path() . '/forcedelete')
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', $attributes);
    }

    public function test_an_authenticated_user_cannot_see_tasks_of_others()
    {
        $this->apiSignIn();

        $task = factory(Task::class)->create();

        $this->get($task->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_update_tasks_of_others()
    {
        $this->apiSignIn();

        $task = factory(Task::class)->create();

        $this->patchJson($task->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_delete_tasks_of_others()
    {
       $this->apiSignIn();

        $task = factory(Task::class)->create();

        $this->deleteJson($task->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_restore_tasks_of_others()
    {
        $this->apiSignIn();

        $task = factory(Task::class)->create();

        $task->delete();

        $this->patchJson($task->path() . '/restore')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_force_delete_tasks_of_others()
    {
        $this->apiSignIn();

        $task = factory(Task::class)->create();

        $task->delete();

        $this->deleteJson($task->path() . '/forcedelete')->assertForbidden();
    }
}
