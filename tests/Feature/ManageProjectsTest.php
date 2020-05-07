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

        $this->get('/api/projects')->assertRedirect('login');
        $this->post('/api/projects', $project->toArray())->assertRedirect('login');
        $this->get($project->path())->assertRedirect('login');
        $this->patch($project->path())->assertRedirect('login');
        $this->delete($project->path())->assertRedirect('login');
        $this->patch($project->path() . '/restore')->assertRedirect('login');
        $this->delete($project->path() . '/forcedelete')->assertRedirect('login');
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

        $response = $this->actingAs($user)->getJson($project->path())
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

        $response = $this->actingAs($user)->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(3);
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
            ->assertNoContent();

        $this->assertSoftDeleted('projects', $attributes);
    }

    public function test_a_user_can_restore_a_project()
    {
        $user = $this->apiSignIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $project->delete();

        $response = $this->actingAs($user)
            ->patchJson($project->path() . '/restore')
            ->assertOk()
            ->assertJson([
                'message' => 'Project was successfully restored.',
            ]);

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_project()
    {
        $user = $this->apiSignIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $project->delete();

        $this->actingAs($user)
            ->deleteJson($project->path() . '/forcedelete')
            ->assertNoContent();

        $this->assertDatabaseMissing('projects', $attributes);
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

    public function test_an_authenticated_user_cannot_see_projects_of_others()
    {
        $this->apiSignIn();

        $project = factory(Project::class)->create();

        $this->get($project->path())->assertForbidden();
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
}
