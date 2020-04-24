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

        $this->get('/clients')->assertRedirect('login');
        $this->get('/clients/create')->assertRedirect('login');
        $this->post('/clients', $client->toArray())->assertRedirect('login');
        $this->get($client->path())->assertRedirect('login');
        $this->get('/clients/edit')->assertRedirect('login');
        $this->patch($client->path())->assertRedirect('login');
        $this->delete($client->path())->assertRedirect('login');
        $this->patch($client->path() . '/restore')->assertRedirect('login');
        $this->delete($client->path() . '/forcedelete')->assertRedirect('login');
    }

    public function test_a_client_requires_a_name()
    {
        $this->signIn();

        $attributes = factory(Client::class)->raw(['name' => '']);

        $this->post('/clients', $attributes)->assertSessionHasErrors('name');
    }

    public function test_a_user_can_create_a_client()
    {
        $this->signIn();

        $this->get('/clients/create')->assertOk();

        $attributes = ['name' => $this->faker->sentence()];

        $response = $this->post('/clients', $attributes);

        $client = Client::where($attributes)->first();

        $response->assertRedirect($client->path());

        $this->get($client->path())->assertSee($attributes['name']);
    }

    public function test_a_user_can_update_a_client()
    {
        $client = factory(Client::class)->create();

        $this->actingAs($client->user)
            ->patch($client->path(), $attributes = [
                'name' => 'New Name'
            ])
            ->assertRedirect($client->path());

        $this->assertDatabaseHas('clients', $attributes);
    }

    public function test_a_user_can_soft_delete_a_client()
    {
        $user = $this->signIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $this->actingAs($user)
            ->delete($client->path())
            ->assertRedirect('/clients');

        $this->assertSoftDeleted('clients', $attributes);
    }

    public function test_a_user_can_restore_a_client()
    {
        $user = $this->signIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $client->delete();

        $response = $this->actingAs($user)
            ->patch($client->path() . '/restore')
            ->assertRedirect($client->path());

        $this->assertDatabaseHas('clients', $attributes);
    }

    public function test_a_user_can_only_force_delete_a_soft_deleted_client()
    {
        $user = $this->signIn();

        $attributes = ['name' => 'Client Name'];

        $client = $user->clients()->create($attributes);

        $client->delete();

        $this->actingAs($user)
            ->delete($client->path() . '/forcedelete')
            ->assertRedirect('/clients');

        $this->assertDatabaseMissing('clients', $attributes);
    }

    public function test_an_authenticated_user_cannot_see_clients_of_others()
    {
        $this->signIn();

        $client = factory(Client::class)->create();

        $this->get($client->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_update_clients_of_others()
    {
        $this->signIn();

        $client = factory(Client::class)->create();

        $this->patch($client->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_delete_clients_of_others()
    {
        $this->signIn();

        $client = factory(Client::class)->create();

        $this->delete($client->path())->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_restore_clients_of_others()
    {
        $this->signIn();

        $client = factory(Client::class)->create();

        $client->delete();

        $this->patch($client->path() . '/restore')->assertForbidden();
    }

    public function test_an_authenticated_user_cannot_force_delete_clients_of_others()
    {
        $this->signIn();

        $client = factory(Client::class)->create();

        $client->delete();

        $this->delete($client->path() . '/forcedelete')->assertForbidden();
    }
}
