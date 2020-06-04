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

        $this->getJson('api/tasks')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->postJson($task->project->path() . '/tasks', $task->toArray())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->getJson($task->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->patchJson($task->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->deleteJson($task->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->patchJson($task->path() . '/restore')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->deleteJson($task->path() . '/forcedelete')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->getJson($task->path() . '/activity')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
    }

    public function test_a_user_can_get_a_single_task()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $task = $project->addTask([
            'title' => 'task 1'
        ]);

        $this->assertCount(1, $user->projects);
        $this->assertCount(1, $user->tasks);

        $response = $this->actingAs($user)
            ->getJson($user->tasks->first()->path())
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'task 1'
            ]);
    }

    public function test_a_user_can_get_all_of_their_tasks()
    {        
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $project->addTask(['title' => 'task 1']);
        $project->addTask(['title' => 'task 2']);
        $project->addTask(['title' => 'task 3']);

        $this->assertCount(3, $user->tasks);

        $response = $this->actingAs($user)
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_a_user_can_get_a_projects_tasks()
    {        
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $project->addTask(['title' => 'task 1']);
        factory(Task::class)->create(['title' => 'other 1']);
        $project->addTask(['title' => 'task 2']);
        factory(Task::class)->create(['title' => 'other 2']);
        $project->addTask(['title' => 'task 3']);

        $this->assertCount(3, $user->tasks);

        $response = $this->actingAs($user)
            ->getJson($project->path() . '/tasks')
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'task 1',
                'title' => 'task 2',
                'title' => 'task 3'
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

        $task_attr = ['title' => $this->faker->sentence()];

        $this->postJson($project->path() . '/tasks/', $task_attr)
            ->assertCreated()
            ->assertJson([
                'message' => 'Task was successfully created.',
            ]);

        $this->assertDatabaseHas('tasks', $task_attr);
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
            ->assertOk()
            ->assertJson([
                'message' => 'Task was successfully trashed.',
            ]);

        $this->assertSoftDeleted('tasks', $attributes);
    }

    public function test_a_user_can_restore_their_soft_deleted_tasks()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project']);

        $attributes = ['title' => 'Task Title'];

        $task = $project->addTask($attributes);

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

        $task = $project->addTask($attributes);

        $task->delete();

        $this->actingAs($user)
            ->deleteJson($task->path() . '/forcedelete')
            ->assertOk()
            ->assertJson([
                'message' => 'Task was permanently deleted.',
            ]);

        $this->assertDatabaseMissing('tasks', $attributes);
        $this->assertDatabaseMissing('activities', [
            'subject_id'   => $task->id,
            'subject_type' => get_class($task)
        ]);
    }

    public function test_a_user_can_see_the_tasks_activity()
    {
        $user = $this->apiSignIn();

        $project = factory(Project::class)->create([
            'user_id' => $user->id
        ]);

        $task = $project->addTask(['title' => 'task 1']);
        $task->update(['title' => 'New title']);

        $this->assertCount(2, $task->activity);

        $response = $this->actingAs($user)
            ->getJson($task->path() . '/activity')
            ->assertOk()
            ->assertJsonFragment([
                'description' => 'created_task',
                'description' => 'updated_task'
            ]);

        $this->assertEquals('created_task', $task->activity->first()->description);
        $this->assertEquals('updated_task', $task->activity->last()->description);
    }

    public function test_an_authenticated_user_cannot_see_tasks_of_others()
    {
        $this->apiSignIn();

        $task = factory(Task::class)->create();

        $this->getJson($task->path())->assertForbidden();
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

    public function test_an_authenticated_user_cannot_see_other_tasks_activity()
    {
        $this->apiSignIn();

        $task = factory(Task::class)->create();

        $this->getJson($task->path() . '/activity')->assertForbidden();
    }
}
