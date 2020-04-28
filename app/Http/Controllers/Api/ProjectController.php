<?php

namespace App\Http\Controllers\Api;

use App\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

        return response()->json($project, 201);
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

        return response()->json($project);
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

        return response()->json($project, 200);
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

        $project->delete();

        return response()->json(null, 204);
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

        return response()->json($project, 200);
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

        return response()->json(null, 204);
    }

    /**
     * Validate the request.
     *
     * @return Illuminate\Http\Request
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'client_id' => [
                'sometimes',
                Rule::in(auth()->user()->clients()->pluck('id'))
            ],
            'title' => 'required'
        ]);
    }
}