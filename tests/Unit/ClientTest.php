<?php

namespace Tests\Unit;

use App\User;
use App\Client;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_belongs_to_a_user()
    {
        $client = factory(Client::class)->create();

        $this->assertInstanceOf(User::class, $client->user);
    }

    public function test_a_client_has_projects()
    {
        $client = factory(Client::class)->create();

        $this->assertInstanceOf(Collection::class, $client->projects);
    }

    public function test_a_client_has_a_path()
    {
        $client = factory(Client::class)->create();

        $this->assertEquals(
            $client->path(),
            "/api/clients/{$client->id}"
        );
    }

    public function test_a_client_can_be_soft_deleted()
    {
        $client = factory(Client::class)->create();

        $client->delete();

        $this->assertSoftDeleted('clients', [
            'id' => $client->id,
        ]);
    }

    public function test_a_client_can_be_restored()
    {
        $client = factory(Client::class)->create();

        $client->delete();

        $client->restore();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
        ]);
    }

    public function test_a_client_can_be_force_deleted()
    {
        $client = factory(Client::class)->create();

        $client->delete();

        $client->forcedelete();

        $this->assertDatabaseMissing('clients', [
            'id' => $client->id,
        ]);
    }
}
