<?php

namespace Tests\Unit;

use App\Task;
use App\Project;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_task_belongs_to_a_project()
    {
        $task = factory(Task::class)->create();
        
        $this->assertInstanceOf(Project::class, $task->project);
    }

    public function test_a_task_has_a_path()
    {
        $task = factory(Task::class)->create();

        $this->assertEquals(
            $task->path(),
            "{$task->project->path()}/tasks/{$task->id}"
        );
    }

    public function test_a_task_can_be_marked_as_complete()
    {
        $task = factory(Task::class)->create();

        $this->assertNull($task->completed_at);

        $task->complete();

        $this->assertNotNull($task->completed_at);
    }

    public function test_a_task_can_be_marked_as_incomplete()
    {
        $task = factory(Task::class)->create(['completed_at' => Carbon::now()->toDateTimeString()]);

        $this->assertNotNull($task->completed_at);

        $task->incomplete();

        $this->assertNull($task->completed_at);
    }

    public function test_a_task_can_be_marked_as_billed()
    {
        $task = factory(Task::class)->create();

        $this->assertNull($task->billed_at);

        $task->billed();
        
        $this->assertNotNull($task->fresh()->billed_at);
    }

    public function test_a_task_can_be_marked_as_unbilled()
    {
        $task = factory(Task::class)->create(['billed_at' => Carbon::now()->toDateTimeString()]);

        $this->assertNotNull($task->billed_at);

        $task->unbilled();

        $this->assertNull($task->billed_at);
    }

    public function test_a_task_can_be_soft_deleted()
    {
        $task = factory(Task::class)->create();

        $task->delete();

        $this->assertSoftDeleted('tasks', [
            'id'    => $task->id
        ]);
    }

    public function test_a_task_can_be_restored()
    {
        $task = factory(Task::class)->create();

        $task->delete();

        $task->restore();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_a_task_can_be_force_deleted()
    {
        $task = factory(Task::class)->create();

        $task->delete();

        $task->forcedelete();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
