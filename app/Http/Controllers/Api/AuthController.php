<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login()
    {
        $user = User::where('email', request('username'))->first();

        abort_unless($user, 404, 'These credentials do not match our records.');
        abort_unless(
            \Hash::check(request('password'), $user->password),
            403,
            'These credentials do not match our records.'
        );

        $response = $this->grantPasswordToken(request('username'), request('password'));

        return response($response, 200);
    }

    public function register()
    {
        $this->validate(request(), [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => request('name'),
            'email'    => request('username'),
            'password' => Hash::make(request('password')),
        ]);

        $response = $this->grantPasswordToken(
            $user->email,
            request('password')
        );

        return response($response, 201);
    }

    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return response(200);
    }

    protected function grantPasswordToken(string $username, string $password)
    {
        $params = [
            'grant_type' => 'password',
            'client_id' => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'username' => $username,
            'password' => $password,
            'scope' => '',
        ];

        $response = Http::post(config('services.passport.login_endpoint'), $params);

        return $response->getBody();
    }
}
