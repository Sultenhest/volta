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
}
