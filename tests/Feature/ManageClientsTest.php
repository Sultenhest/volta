<?php

namespace Tests\Feature;

use App\Client;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageClientsTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_guests_cannot_manage_clients()
    {
        $client = factory(Client::class)->create();

        $this->get('/api/clients')->assertRedirect('login');
        $this->post('/api/clients', $client->toArray())->assertRedirect('login');
        $this->get($client->path())->assertRedirect('login');
        $this->patch($client->path())->assertRedirect('login');
        $this->delete($client->path())->assertRedirect('login');
        $this->patch($client->path() . '/restore')->assertRedirect('login');
        $this->delete($client->path() . '/forcedelete')->assertRedirect('login');
    }

    public function test_a_client_requires_a_name()
    {
        $this->apiSignIn();

        $attributes = factory(Client::class)->raw(['name' => '']);

        $response = $this->postJson('/api/clients', $attributes);

        $response->assertStatus(422)->assertJson([
            'message' => 'The given data was invalid.'
        ]);
    }

    public function test_a_user_can_create_a_client()
    {
        $user = $this->apiSignIn();

        $response = $this->postJson('/api/clients', $attributes = [
                'name' => $this->faker->sentence()
            ])
            ->assertCreated();

        //$client = Client::where($attributes)->first();

        $this->assertDatabaseHas('clients', $attributes);
    }

    public function test_a_user_can_update_their_client()
    {
        $client = factory(Client::class)->create();

        $user = $this->apiSignIn($client->user);

        $response = $this->actingAs($user)
            ->patchJson($client->path(), $attributes = [
                'name' => 'New Name'
            ])
            ->assertOk();

        $this->assertDatabaseHas('clients', $attributes);
    }

    public function test_a_user_can_soft_delete_their_client()
    {
        $user = $this->apiSignIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $response = $this->actingAs($user)
            ->deleteJson($client->path())
            ->assertNoContent();

        $this->assertSoftDeleted('clients', $attributes);
    }

    public function test_a_user_can_restore_their_client()
    {
        $user = $this->apiSignIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $client->delete();

        $response = $this->actingAs($user)
            ->patchJson($client->path() . '/restore')
            ->assertOk();

        $this->assertDatabaseHas('clients', $attributes);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_client()
    {
        $user = $this->apiSignIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $client->delete();

        $this->actingAs($user)
            ->deleteJson($client->path() . '/forcedelete')
            ->assertStatus(204);

        $this->assertDatabaseMissing('clients', $attributes);
    }

    public function test_an_authenticated_user_cannot_see_clients_of_others()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $this->get($client->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_update_clients_of_others()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $this->patchJson($client->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_delete_clients_of_others()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $this->deleteJson($client->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_restore_clients_of_others()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $client->delete();

        $this->patchJson($client->path() . '/restore')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_force_delete_clients_of_others()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $client->delete();

        $this->deleteJson($client->path() . '/forcedelete')->assertForbidden();
    }
}
