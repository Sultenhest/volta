<?php

namespace App\Http\Controllers;

use App\Task;
use App\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function index(Project $project)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function create(Project $project)
    {
        //
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
        $task = $project->tasks()->create($this->validateRequest());

        return redirect($task->path());
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

        return view('tasks.show', compact('project', 'task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project, Task $task)
    {
        //
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
        $this->authorize('view', $project);

        $task->update($this->validateRequest());

        return redirect($task->path());
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

        return redirect($project->path());
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

        return redirect($task->path());
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

        return redirect($project->path());
    }

    /**
     * Validate the request.
     *
     * @return Illuminate\Http\Request
     */
    protected function validateRequest()
    {
        return request()->validate([
            /*
            'project_id' => [
                'sometimes',
                Rule::in(auth()->user()->projects()->pluck('id'))
            ],
            */
            'title'         => 'required',
            'hours_spent'   => 'sometimes|integer|min:0',
            'minutes_spent' => 'sometimes|integer|min:0|max:59',
        ]);
    }
}
