<?php

namespace App\Http\Controllers\Api;

use App\Client;
use App\Http\Resources\ClientCollection;
use App\Http\Resources\ActivityCollection;
use App\Http\Resources\Client as ClientResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $clients = auth()->user()->clients()->get();
        $trashed_clients = auth()->user()->clients()->onlyTrashed()->get();
        
        return response()->json([
            'clients'         => new ClientCollection($clients),
            'trashed_clients' => new ClientCollection($trashed_clients)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $client = auth()->user()->clients()->create($this->validateRequest($request));

        return response()->json([
            'client'  => new ClientResource($client),
            'message' => 'Client was successfully created.'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->update($this->validateRequest($request));

        return response()->json([
            'client'  => new ClientResource($client),
            'message' => 'Client was successfully updated.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return response()->json([
            'message' => 'Client was successfully trashed.'
        ], 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function restore(Client $client)
    {
        $this->authorize('restore', $client);

        $client->restore();

        return response()->json([
            'client'  => new ClientResource($client),
            'message' => 'Client was successfully restored.'
        ], 200);
    }

    /**
     * Force delete the specified resource from storage.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function forceDelete(Client $client)
    {
        $this->authorize('forceDelete', $client);

        $client->forceDelete();

        return response()->json([
            'message' => 'Client was permanently deleted.'
        ], 204);
    }

    /**
     * Display the resource activities.
     *
     * @param  \App\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function activity(Client $client)
    {
        $this->authorize('view', $client);

        $activities = $client->activity()->paginate(20);

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
            'name'        => 'sometimes|required',
            'description' => 'nullable',
            'vat_abbr'    => 'string|max:2|nullable',
            'vat'         => 'numeric|nullable'
        ]);
    }
}
