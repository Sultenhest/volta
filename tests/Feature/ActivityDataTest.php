<?php

namespace Tests\Feature;

use Carbon\Carbon;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ActivityDataTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_guests_cannot_access_activity()
    {
        $this->get('/api/activities')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
    }

    public function test_a_user_can_access_their_feed()
    {
        $user = $this->apiSignIn();

        $response = $this->actingAs($user)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonFragment([
                'feed' => []
            ]);
    }

    public function test_a_user_can_get_all_their_activities()
    {
        $user = $this->apiSignIn();

        $user->projects()->create(['title' => 'project 1']);
        $user->projects()->create(['title' => 'project 2']);
        $user->projects()->create(['title' => 'project 3']);

        $response = $this->actingAs($user)
            ->getJson('/api/activities')
            ->assertOk()
            ->assertJsonCount(3);
    }
}
