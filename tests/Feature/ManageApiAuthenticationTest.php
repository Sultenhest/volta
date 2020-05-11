<?php

namespace Tests\Feature;

use App\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Passport\Passport;

class ManageApiAuthenticationTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_a_user_can_access_their_user_info()
    {
        $this->apiSignIn();

        $response = $this->get('/api/user');

        $response->assertStatus(200);
    }

    public function an_unauthenticated_user_cannot_access_user_information()
    {
        $this->get('/api/user')->assertResponseStatus(403);
    }

    public function test_a_user_can_register()
    {
        $response = $this->postJson('/api/register', [ 
            'name'     => $this->faker->name,
            'username' => $this->faker->email(),
            'password' => $this->faker->password()
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'message' => 'You were successfully registered!'
            ]);
    }

    public function test_registration_requires_email_and_password()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['username', 'password']);
    }

    // TODO
    public function a_user_can_log_in()
    {
        $response = $this->postJson('/api/login', [
            'username' => $this->faker->email(),
            'password' => $this->faker->password()
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success' => ['token']
            ]);
    }
}
