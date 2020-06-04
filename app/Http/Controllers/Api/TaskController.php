<?php

namespace App\Http\Controllers\Api;

use App\Task;
use App\Project;
use App\Http\Resources\Project as ProjectResource;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\ActivityCollection;
use App\Http\Resources\Task as TaskResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project = null)
    {   
        if( is_null( $project ) ) {
            $tasks = auth()->user()->tasks()->paginate(10);
        } else {
            $this->authorize('view', $project);
            $tasks = $project->tasks;
        }
        
        return new TaskCollection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $task = $project->addTask($this->validateRequest($request));

        return response()->json([
            'task'    => new TaskResource($task),
            'message' => 'Task was successfully created.'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $project);

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project, Task $task)
    {
        $this->authorize('update', $project);

        $task->update($this->validateRequest($request));

        return response()->json([
            'task'    => new TaskResource($task),
            'project' => new ProjectResource($project),
            'message' => 'Task was successfully updated.'
        ], 200);
    }

   /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project, Task $task)
    {
        $this->authorize('update', $project);
        
        $task->delete();

        return response()->json([
            'message' => 'Task was successfully trashed.'
        ], 200);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function restore(Project $project, Task $task)
    {
        $this->authorize('update', $project);
        
        $task->restore();

        return response()->json([
            'task'    => new TaskResource($task),
            'message' => 'Task was successfully restored.'
        ], 200);
    }

    /**
     * Force delete the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(Project $project, Task $task)
    {
        $this->authorize('update', $project);

        $this->authorize('forceDelete', $task);
        
        $task->forceDelete();

        return response()->json([
            'message' => 'Task was permanently deleted.'
        ], 200);
    }

    /**
     * Toggle completed_at on the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function completed(Project $project, Task $task) 
    {
        $this->authorize('update', $project);

        if( is_null( $task->completed_at ) ) {
            $task->complete();
            $message = 'complete!';
        } else {
            $task->incomplete();
            $message = 'incomplete!';
        }

        return response()->json([
            'task'    => new TaskResource($task),
            'message' => 'Task was successfully marked as ' . $message
        ], 200);
    }

    /**
     * Toggle billed_at on the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function billed(Project $project, Task $task) 
    {
        $this->authorize('update', $project);

        if( is_null( $task->billed_at ) ) {
            $task->billed();
            $message = 'billed!';
        } else {
            $task->unbilled();
            $message = 'unbilled!';
        }

        return response()->json([
            'task'    => new TaskResource($task),
            'message' => 'Task was successfully marked as ' . $message
        ], 200);
    }

    /**
     * Display the resource activities.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function activity(Project $project, Task $task) 
    {
        $this->authorize('view', $project);

        $activities = $task->activity()->paginate(20);

        return new ActivityCollection($activities);
    }

    /**
     * Validate the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\Request
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'project_id' => [
                'sometimes',
                Rule::in(auth()->user()->projects()->pluck('id'))
            ],
            'title'         => 'required|max:255',
            'description'   => 'nullable|max:5000',
            'hours_spent'   => 'nullable|sometimes|integer|min:0',
            'minutes_spent' => 'nullable|sometimes|integer|min:0|max:59'
        ]);
    }
}
