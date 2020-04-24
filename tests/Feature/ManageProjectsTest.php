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

        $this->get('/projects')->assertRedirect('login');
        $this->get('/projects/create')->assertRedirect('login');
        $this->post('/projects', $project->toArray())->assertRedirect('login');
        $this->get($project->path())->assertRedirect('login');
        $this->get('/projects/edit')->assertRedirect('login');
        $this->patch($project->path())->assertRedirect('login');
        $this->delete($project->path())->assertRedirect('login');
        $this->patch($project->path() . '/restore')->assertRedirect('login');
        $this->delete($project->path() . '/forcedelete')->assertRedirect('login');
    }

    public function test_a_project_requires_a_title()
    {
        $this->signIn();

        $attributes = factory(Project::class)->raw(['title' => '']);

        $this->post('/projects', $attributes)->assertSessionHasErrors('title');
    }

    public function test_a_user_can_create_a_project()
    {
        $this->signIn();

        $this->get('/projects/create')->assertOk();

        $attributes = ['title' => $this->faker->sentence()];

        $response = $this->post('/projects', $attributes);

        $project = Project::where($attributes)->first();

        $response->assertRedirect($project->path());

        $this->get($project->path())->assertSee($attributes['title']);
    }

    public function test_a_user_can_update_a_project()
    {
        $project = factory(Project::class)->create();

        $this->actingAs($project->user)
            ->patch($project->path(), $attributes = [
                'title' => 'New Title'
            ])
            ->assertRedirect($project->path());

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_can_soft_delete_a_project()
    {
        $user = $this->signIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $this->actingAs($user)
            ->delete($project->path())
            ->assertRedirect('/projects');

        $this->assertSoftDeleted('projects', $attributes);
    }

    public function test_a_user_can_restore_a_project()
    {
        $user = $this->signIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $project->delete();

        $response = $this->actingAs($user)
            ->patch($project->path() . '/restore')
            ->assertRedirect($project->path());

        $this->assertDatabaseHas('projects', $attributes);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_project()
    {
        $user = $this->signIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $project->delete();

        $this->actingAs($user)
            ->delete($project->path() . '/forcedelete')
            ->assertRedirect('/projects');

        $this->assertDatabaseMissing('projects', $attributes);
    }

    public function test_a_user_can_view_their_project()
    {
        $project = factory(Project::class)->create();

        $this->actingAs($project->user)
            ->get($project->path())
            ->assertSee($project->title);
    }

    public function test_a_user_can_add_their_own_clients_to_projects()
    {
        $user = $this->signIn();

        $client = $user->clients()->create(['name' => 'Test name']);

        $attributes = [
            'client_id' => $client->id,
            'title'     => $this->faker->sentence()
        ];

        $response = $this->post('/projects', $attributes);

        $project = Project::where($attributes)->first();

        $response->assertRedirect($project->path());

        $this->get($project->path())->assertSee($attributes['title']);
        $this->get($project->path())->assertSee($project->client->name);
    }

    public function test_a_user_cannot_add_other_users_clients_to_projects()
    {
        $user = $this->signIn();

        $attributes = ['title' => 'Project Title'];

        $project = $user->projects()->create($attributes);

        $client = factory(Client::class)->create();

        $this->actingAs($user)
            ->patch($project->path(), [
                'client_id' => $client->id
            ])
            ->assertSessionHasErrors('client_id');
    }

    public function test_an_authenticated_user_cannot_see_projects_of_others()
    {
        $this->signIn();

        $project = factory(Project::class)->create();

        $this->get($project->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_update_projects_of_others()
    {
        $this->signIn();

        $project = factory(Project::class)->create();

        $this->patch($project->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_delete_projects_of_others()
    {
        $this->signIn();

        $project = factory(Project::class)->create();

        $this->delete($project->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_restore_projects_of_others()
    {
        $this->signIn();

        $project = factory(Project::class)->create();

        $project->delete();

        $this->patch($project->path() . '/restore')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_force_delete_projects_of_others()
    {
        $this->signIn();

        $project = factory(Project::class)->create();

        $project->delete();

        $this->delete($project->path() . '/forcedelete')->assertForbidden();
    }
}
