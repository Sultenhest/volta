<?php

namespace App\Http\Controllers\Api;

use App\Project;
use App\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projectsInProgressIds = Redis::zrevrange('user.' . auth()->id() . '.projectsInProgress', 0, 2);

        $projectsInProgress = collect($projectsInProgressIds)->map(function ($id) {
            return Project::find($id);
        });

        return response()->json([
            'projects'   => $projectsInProgress,
            'statistics' => Activity::statistics(),
            'message'    => 'Yeah man'
        ], 200);
    }
}
