<?php

namespace App\Http\Controllers\Api;

use App\Task;
use App\Project;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return auth()->user()->tasks;
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
        $task = $project->tasks()->create($this->validateRequest($request));

        return response()->json([
            'task'    => $task,
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

        return response()->json($task);
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
            'task'    => $task,
            'project' => $project,
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
        ], 204);
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
            'task'    => $task,
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
        ], 204);
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
            'task'    => $task,
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
            'task'    => $task,
            'message' => 'Task was successfully marked as ' . $message
        ], 200);
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
            'title'         => 'required',
            'description'   => 'nullable',
            'hours_spent'   => 'sometimes|integer|min:0|nullable',
            'minutes_spent' => 'sometimes|integer|min:0|max:59|nullable'
        ]);
    }
}
