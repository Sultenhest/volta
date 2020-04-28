<?php

namespace Tests\Feature;

use App\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;

class ManageApiAuthentication extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_a_user_can_access_their_user_info()
    {
        $this->apiSignIn();

        $response = $this->get('/api/user');

        $response->assertStatus(200);
    }

    public function test_an_unauthenticated_user_cannot_access_user_information()
    {
        $this->get('/api/user')->assertRedirect('login');
    }
}
