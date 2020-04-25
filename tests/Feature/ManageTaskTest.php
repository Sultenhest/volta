<?php

namespace Tests\Feature;

use App\Task;
use App\Project;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageTaskTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_guests_cannot_manage_tasks()
    {
        $task = factory(Task::class)->create();

        $this->get($task->project->path() . '/tasks')->assertRedirect('login');
        $this->get($task->project->path() . '/tasks/create')->assertRedirect('login');
        $this->post($task->project->path() . '/tasks', $task->toArray())->assertRedirect('login');
        $this->get($task->path())->assertRedirect('login');
        $this->get($task->path() . '/edit')->assertRedirect('login');
        $this->patch($task->path())->assertRedirect('login');
        $this->delete($task->path())->assertRedirect('login');
        $this->patch($task->path() . '/restore')->assertRedirect('login');
        $this->delete($task->path() . '/forcedelete')->assertRedirect('login');
    }

    public function test_a_task_requires_a_title()
    {
        $this->signIn();

        $attributes = factory(Task::class)->raw(['title' => '']);

        $project = factory(Project::class)->create();

        $this->post($project->path() . '/tasks', $attributes)->assertSessionHasErrors('title');
    }

    public function test_a_task_hours_spent_has_to_be_atleast_0()
    {
        $this->signIn();

        $attributes = factory(Task::class)->raw([
            'title'       => $this->faker->sentence(),
            'hours_spent' => -1
        ]);

        $project = factory(Project::class)->create();

        $this->post($project->path() . '/tasks', $attributes)->assertSessionHasErrors('hours_spent');
    }

    public function test_a_task_minutes_spent_has_to_be_atleast_0()
    {
        $this->signIn();

        $attributes = factory(Task::class)->raw([
            'title'         => $this->faker->sentence(),
            'minutes_spent' => -1
        ]);

        $project = factory(Project::class)->create();

        $this->post($project->path() . '/tasks', $attributes)->assertSessionHasErrors('minutes_spent');
    }

    public function test_a_task_minutes_spent_has_to_be_below_60()
    {
        $this->signIn();

        $attributes = factory(Task::class)->raw([
            'title'         => $this->faker->sentence(),
            'minutes_spent' => 60
        ]);

        $project = factory(Project::class)->create();

        $this->post($project->path() . '/tasks', $attributes)->assertSessionHasErrors('minutes_spent');
    }

    public function test_a_user_can_add_tasks_to_their_project()
    {
        $project = factory(Project::class)->create();

        $this->actingAs($project->user)
            ->get($project->path() . '/tasks/create')->assertOk();

        $attributes = ['title' => $this->faker->sentence()];

        $response = $this->actingAs($project->user)
                        ->post($project->path() . '/tasks', $attributes);

        $task = Task::where($attributes)->first();

        $response->assertRedirect($task->path());

        $this->get($task->path())->assertSee($attributes['title']);
    }

    public function test_a_user_can_update_their_task()
    {
        $task = factory(Task::class)->create();

        $this->actingAs($task->project->user)
            ->patch($task->path(), $attributes = [
                'title' => 'new title'
            ])
            ->assertRedirect($task->path());

        $this->assertDatabaseHas('tasks', $attributes);
    }

    public function test_a_user_can_restore_their_soft_deleted_task()
    {
        $project = factory(Project::class)->create();

        $attributes = ['title' => $this->faker->sentence()];

        $task = $project->tasks()->create($attributes);

        $this->actingAs($project->user)
            ->delete($task->path())
            ->assertRedirect($project->path());

        $this->assertSoftDeleted('tasks', $attributes);

        $this->actingAs($project->user)
            ->patch($task->path() . '/restore')
            ->assertRedirect($task->path());

        $this->assertDatabaseHas('tasks', $attributes);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_task()
    {
        $project = factory(Project::class)->create();

        $attributes = ['title' => $this->faker->sentence()];

        $task = $project->tasks()->create($attributes);

        $this->actingAs($project->user)
            ->delete($task->path() . '/forcedelete')
            ->assertForbidden();

        $task->delete();

        $this->assertSoftDeleted('tasks', $attributes);

        $this->actingAs($project->user)
            ->delete($task->path() . '/forcedelete')
            ->assertRedirect($project->path());

        $this->assertDatabaseMissing('tasks', $attributes);
    }

    public function test_an_authenticated_user_cannot_see_tasks_of_others()
    {
        $this->signIn();

        $task = factory('App\Task')->create();

        $this->get($task->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_update_tasks_of_others()
    {
        $this->signIn();

        $task = factory('App\Task')->create();

        $this->patch($task->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_delete_tasks_of_others()
    {
        $this->signIn();

        $task = factory('App\Task')->create();

        $this->delete($task->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_restore_tasks_of_others()
    {
        $this->signIn();

        $task = factory('App\Task')->create();

        $task->delete();

        $this->patch($task->path() . '/restore')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_force_delete_tasks_of_others()
    {
        $this->signIn();

        $task = factory('App\Task')->create();

        $task->delete();

        $this->delete($task->path() . '/forcedelete')->assertForbidden();
    }
}
