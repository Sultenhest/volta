<?php

namespace Tests\Unit;

use App\User;

use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_has_clients()
    {
        $user = factory(User::class)->create();

        $this->assertInstanceOf(Collection::class, $user->clients);
    }

    public function test_a_user_has_projects()
    {
        $user = factory(User::class)->create();

        $this->assertInstanceOf(Collection::class, $user->projects);
    }

    public function test_a_user_has_tasks()
    {
        $user = factory(User::class)->create();

        $this->assertInstanceOf(Collection::class, $user->tasks);
    }
}