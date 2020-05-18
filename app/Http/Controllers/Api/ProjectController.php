<?php

namespace App\Http\Controllers\Api;

use App\Project;
use App\Http\Resources\ProjectCollection;
use App\Http\Resources\ActivityCollection;
use App\Http\Resources\Project as ProjectResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redis;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = auth()->user()->projects()->paginate(10);
        
        return new ProjectCollection($projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $project = auth()->user()->projects()->create($this->validateRequest($request));

        return response()->json([
            'project' => new ProjectResource($project),
            'message' => 'Project was successfully created.'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        Redis::zadd('user.' . auth()->id() . '.projectsInProgress', time(), $project->id);

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($this->validateRequest($request));

        return response()->json([
            'project' => new ProjectResource($project),
            'message' => 'Project was successfully updated.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        Redis::zrem('user.' . auth()->id() . '.projectsInProgress', $project->id);

        $project->delete();

        return response()->json([
            'message' => 'Project was successfully trashed.'
        ], 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function restore(Project $project)
    {
        $this->authorize('restore', $project);

        $project->restore();

        return response()->json([
            'project' => new ProjectResource($project),
            'message' => 'Project was successfully restored.'
        ], 200);
    }

    /**
     * Force delete the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(Project $project)
    {
        $this->authorize('forceDelete', $project);

        $project->forceDelete();

        return response()->json([
            'message' => 'Project was permanently deleted.'
        ], 204);
    }

    /**
     * Display the resource activities.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function activity(Project $project)
    {
        $this->authorize('view', $project);

        $activities = $project->activity()->paginate(20);

        return new ActivityCollection($activities);
    }

    /**
     * Validate the request.
     *
     * @return Illuminate\Http\Request
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'client_id'   => [
                'sometimes',
                Rule::in(auth()->user()->clients()->pluck('id')),
                'nullable'
            ],
            'title'       => 'required',
            'description' => 'nullable',
        ]);
    }
}
