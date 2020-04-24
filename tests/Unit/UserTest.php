<?php

namespace Tests\Unit;

use App\User;
use App\Client;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_has_clients()
    {
        $user = $this->signIn();

        $this->assertInstanceOf(Collection::class, $user->clients);
    }

    public function test_a_user_has_projects()
    {
        $user = $this->signIn();

        $this->assertInstanceOf(Collection::class, $user->projects);
    }
}