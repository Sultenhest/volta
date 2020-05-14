<?php

namespace App\Http\Controllers\Api;

use App\Activity;
use App\Http\Resources\ActivityCollection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $activities = auth()->user()->activity()->paginate(20);
        
        return new ActivityCollection($activities);
    }
}
