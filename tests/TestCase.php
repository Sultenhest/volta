<?php

namespace Tests;

use App\User;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function apiSignIn($user = null)
    {
        $user = $user ?: factory(User::class)->create();

        return Passport::actingAs($user);
    }
}