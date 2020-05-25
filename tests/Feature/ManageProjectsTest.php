<?php

namespace Tests\Feature;

use App\Project;
use App\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageProjectsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_guests_cannot_manage_clients()
    {
        $project = factory(Project::class)->create();

        $this->getJson('/api/projects')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->postJson('/api/projects', $project->toArray())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->getJson($project->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->patchJson($project->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->deleteJson($project->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->patchJson($project->path() . '/restore')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->deleteJson($project->path() . '/forcedelete')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->getJson($project->path() . '/activity')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
    }

    public function test_a_user_can_get_a_single_project()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'project 1']);

        $project->addTask(['title' => 'task 1']);
        $project->addTask(['title' => 'task 2'])->complete();
        $project->addTask(['title' => 'task 3']);

        $this->assertCount(3, $project->tasks);
        $this->assertCount(1, $user->projects);

        $response = $this->actingAs($user)
            ->getJson($project->path())
            ->assertOk()
            ->assertJsonFragment([
                'title'             => 'project 1',
                'tasks_count'       => 3,
                'completed_tasks'   => 1,
                'incompleted_tasks' => 2
            ]);
    }

    public function test_a_user_can_get_all_of_their_projects()
    {        
        $user = $this->apiSignIn();

        $user->projects()->create(['title' => 'project 1']);
        factory(Project::class)->create(['title' => 'other users project 1']);
        $user->projects()->create(['title' => 'project 2']);
        factory(Project::class)->create(['title' => 'other users project 2']);
        $user->projects()->create(['title' => 'project 3']);

        $this->assertCount(3, $user->projects);
        $this->assertCount(5, Project::all());

        $response = $this->actingAs($user)
            ->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_a_user_can_get_a_clients_projects()
    {        
        $user = $this->apiSignIn();

        $client = factory(Client::class)->create([
            'user_id' => $user->id
        ]);

        $user->projects()->create(['title' => 'project 1', 'client_id' => $client->id]);
        factory(Project::class)->create(['title' => 'other 1']);
        $user->projects()->create(['title' => 'project 2', 'client_id' => $client->id]);
        factory(Project::class)->create(['title' => 'other 2']);
        $user->projects()->create(['title' => 'project 3', 'client_id' => $client->id]);

        $this->assertCount(3, $client->projects);

        $response = $this->actingAs($user)
            ->getJson($client->path() . '/projects')
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'project 1',
                'title' => 'project 2',
                'title' => 'project 3'
            ]);
    }

    public function test_a_project_requires_a_title()
    {
        $this->apiSignIn();

        $attributes = factory(Project::class)->raw(['title' => '']);

        $response = $this->postJson('/api/projects', $attributes);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['title']);
    }

    public function test_a_user_can_create_a_project()
    {
        $user = $this->apiSignIn();

        $response = $this->postJson('/api/projects', $attributes = [
                'title' => $this->faker->sentence()
            ])
            ->assertCreated()
            ->assertJson([
                'message' => 'Project was successfully created.',
            ]);

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_can_update_their_project()
    {
        $project = factory(Project::class)->create();

        $user = $this->apiSignIn($project->user);

        $response = $this->actingAs($user)
            ->patchJson($project->path(), $attributes = [
                'title' => 'New Name'
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Project was successfully updated.',
            ]);

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_can_soft_delete_their_project()
    {
        $user = $this->apiSignIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $response = $this->actingAs($user)
            ->deleteJson($project->path())
            ->assertOk()
            ->assertJson([
                'message' => 'Project was successfully trashed.',
            ]);

        $this->assertSoftDeleted('projects', $attributes);
    }

    public function test_soft_deleting_a_project_soft_deletes_its_tasks()
    {
        $user = $this->apiSignIn();

        $project_attr = ['title' => 'Project Title'];
        $task_attr = ['title' => 'Task Title'];

        $project = $user->projects()->create($project_attr);

        $project->addTask($task_attr);

        $this->actingAs($user)
            ->deleteJson($project->path())
            ->assertJson([
                'message' => 'Project was successfully trashed.',
            ]);

        $this->assertSoftDeleted('projects', $project_attr);
        $this->assertSoftDeleted('tasks', $task_attr);
    }

    public function test_a_user_can_restore_a_project()
    {
        $user = $this->apiSignIn();

        $attributes = ['title' => 'Project Title'];
        $task_attr  = ['title' => 'Test Task'];

        $project = $user->projects()->create($attributes);
        $task = $project->addTask($task_attr);

        $project->delete();

        $this->assertSoftDeleted('projects', $attributes);
        $this->assertSoftDeleted('tasks', $task_attr);

        $project->refresh();

        $response = $this->actingAs($user)
            ->patchJson($project->path() . '/restore')
            ->assertOk()
            ->assertJson([
                'message' => 'Project was successfully restored.',
            ]);

        $this->assertDatabaseHas('projects', $attributes);
        $this->assertDatabaseHas('tasks', $task_attr);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_project()
    {
        $user = $this->apiSignIn();

        $attributes = ['title' => 'Project Title'];
        $task_attr  = ['title' => 'Test Task'];

        $project = $user->projects()->create($attributes);
        $project->addTask($task_attr);

        $project->delete();

        $this->actingAs($user)
            ->deleteJson($project->path() . '/forcedelete')
            ->assertOk()
            ->assertJson([
                'message' => 'Project was permanently deleted.',
            ]);

        $this->assertDatabaseMissing('projects', $attributes);
        $this->assertDatabaseMissing('tasks', $task_attr);
        $this->assertDatabaseMissing('activities', [
            'subject_id'   => $project->id,
            'subject_type' => get_class($project)
        ]);
    }

    public function test_a_user_can_add_their_own_clients_to_projects()
    {
        $user = $this->apiSignIn();

        $client = $user->clients()->create(['name' => 'Test name']);

        $attributes = [
            'client_id' => $client->id,
            'title'     => $this->faker->sentence()
        ];

        $response = $this->post('/api/projects', $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_cannot_add_other_users_clients_to_projects()
    {
        $user = $this->apiSignIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $client = factory(Client::class)->create();

        $response = $this->actingAs($user)
            ->patchJson($project->path(), [
                'client_id' => $client->id
            ]);

        $response->assertStatus(422)->assertJson([
            'message' => 'The given data was invalid.'
        ]);
    }

    public function test_a_user_can_see_the_projects_activity()
    {
        $user = $this->apiSignIn();

        $project = $user->projects()->create(['title' => 'New Project']);
        $project->update(['title' => 'New Title']);

        $this->assertCount(2, $project->activity);

        $response = $this->actingAs($user)
            ->getJson($project->path() . '/activity')
            ->assertOk()
            ->assertJsonFragment([
                'description' => 'created_project',
                'description' => 'updated_project'
            ]);

        $this->assertEquals('created_project', $project->activity->first()->description);
        $this->assertEquals('updated_project', $project->activity->last()->description);
    }

    public function test_an_authenticated_user_cannot_see_projects_of_others()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $this->getJson($project->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_update_projects_of_others()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $this->patchJson($project->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_delete_projects_of_others()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $this->deleteJson($project->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_restore_projects_of_others()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $project->delete();

        $this->patchJson($project->path() . '/restore')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_force_delete_projects_of_others()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $project->delete();

        $this->deleteJson($project->path() . '/forcedelete')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_see_other_projects_activity()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $this->getJson($project->path() . '/activity')->assertForbidden();
    }
}
