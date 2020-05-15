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

    /**
     * Register the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $request['email'] = $request['username'];

        $this->validateRequest($request);

        $user = User::create([
            'name'     => $request['name'],
            'email'    => $request['email'],
            'password' => Hash::make($request['password']),
        ]);

        return response()->json([
            'message' => 'You were successfully registered!'
        ], 201);
    }

    /**
     * Validate the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Illuminate\Http\Request
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);
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
