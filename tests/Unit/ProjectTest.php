<?php

namespace Tests\Unit;

use App\User;
use App\Client;
use App\Project;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_project_belongs_to_a_user()
    {
        $project = factory(Project::class)->create();

        $this->assertInstanceOf(User::class, $project->user);
    }

    public function test_a_project_can_belong_to_a_client()
    {
        $project = factory(Project::class)->create();

        $project->update([
            'client_id' => factory(Client::class)->create()->id
        ]);

        $this->assertInstanceOf(Client::class, $project->client);
    }

    public function test_a_project_has_tasks()
    {   
        $project = factory(Project::class)->create();

        $this->assertInstanceOf(Collection::class, $project->tasks);
    }

    public function test_a_project_has_a_path()
    {
        $project = factory(Project::class)->create();

        $this->assertEquals(
            $project->path(),
            "/api/projects/{$project->id}"
        );
    }

    public function test_a_project_can_be_soft_deleted()
    {
        $project = factory(Project::class)->create();

        $project->delete();

        $this->assertSoftDeleted('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_a_client_can_be_restored()
    {
        $project = factory(Project::class)->create();

        $project->delete();

        $project->restore();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_a_project_can_be_force_deleted()
    {
        $project = factory(Project::class)->create();

        $project->delete();

        $project->forcedelete();

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }
}
