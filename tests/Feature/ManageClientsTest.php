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

        $this->getJson('/api/clients')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->postJson('/api/clients', $client->toArray())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->getJson($client->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->patchJson($client->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->deleteJson($client->path())
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->patchJson($client->path() . '/restore')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->deleteJson($client->path() . '/forcedelete')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
        $this->getJson($client->path() . '/activity')
            ->assertUnauthorized()
            ->assertExactJson([
                'error' => 'Unauthenticated. You need to be logged in to access this resource.'
            ]);
    }

    public function test_a_user_can_get_a_single_client()
    {
        $user = $this->apiSignIn();

        $user->clients()->create(['name' => 'client 1']);

        $this->assertCount(1, $user->clients);

        $response = $this->actingAs($user)
            ->getJson($user->clients->first()->path())
            ->assertOk()
            ->assertJsonFragment(['name' => 'client 1']);
    }

    public function test_a_user_can_get_all_of_their_clients()
    {        
        $user = $this->apiSignIn();

        $user->clients()->create(['name' => 'client 1']);
        factory(Client::class)->create(['name' => 'other users client 1']);
        $user->clients()->create(['name' => 'client 2']);
        factory(Client::class)->create(['name' => 'other users client 2']);
        $trash = $user->clients()->create(['name' => 'client 3']);

        $this->assertCount(3, $user->clients);
        $this->assertCount(5, Client::all());

        $trash->delete();

        $response = $this->actingAs($user)
            ->getJson('/api/clients')
            ->assertOk()
            ->assertJsonFragment([
                'name' => 'client 1',
                'name' => 'client 2',
                'name' => 'client 3',
            ]);
    }

    public function test_a_client_requires_a_name()
    {
        $this->apiSignIn();

        $attributes = factory(Client::class)->raw(['name' => '']);

        $response = $this->postJson('/api/clients', $attributes);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.'
            ])
            ->assertJsonValidationErrors(['name']);
    }

    public function test_a_user_can_create_a_client()
    {
        $user = $this->apiSignIn();

        $response = $this->postJson('/api/clients', $attributes = [
                'name'        => $this->faker->sentence(),
                'description' => $this->faker->sentence()
            ])
            ->assertCreated()
            ->assertJson([
                'message' => 'Client was successfully created.',
            ]);

        $this->assertEquals($user->id, Client::first()->user_id);
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
            ->assertOk()
            ->assertJson([
                'message' => 'Client was successfully updated.',
            ]);

        $this->assertDatabaseHas('clients', $attributes);
    }

    public function test_a_user_can_soft_delete_their_client()
    {
        $user = $this->apiSignIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $response = $this->actingAs($user)
            ->deleteJson($client->path())
            ->assertOk()
            ->assertJson([
                'message' => 'Client was successfully trashed.',
            ]);

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
            ->assertOk()
            ->assertJson([
                'message' => 'Client was successfully restored.',
            ]);

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
            ->assertOk()
            ->assertJson([
                'message' => 'Client was permanently deleted.',
            ]);

        $this->assertDatabaseMissing('clients', $attributes);
        $this->assertDatabaseMissing('activities', [
            'subject_id'   => $client->id,
            'subject_type' => get_class($client)
        ]);
    }

    public function test_a_user_can_see_the_clients_activity()
    {
        $user = $this->apiSignIn();

        $client = $user->clients()->create(['name' => 'client 1']);
        $client->update(['name' => 'New Name']);

        $this->assertCount(2, $client->activity);

        $response = $this->actingAs($user)
            ->getJson($client->path() . '/activity')
            ->assertOk()
            ->assertJsonFragment([
                'description' => 'created_client',
                'description' => 'updated_client'
            ]);

        $this->assertEquals('created_client', $client->activity->first()->description);
        $this->assertEquals('updated_client', $client->activity->last()->description);
    }

    public function test_an_authenticated_user_cannot_see_clients_of_others()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $this->getJson($client->path())->assertForbidden();
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

    public function test_an_authenticated_user_cannot_see_other_clients_activity()
    {
        $this->apiSignIn();

        $client = factory(Client::class)->create();

        $this->getJson($client->path() . '/activity')->assertForbidden();
    }
}
