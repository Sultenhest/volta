<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api', 'middleware' => 'guest'], function () {
    Route::get('/version', function () {
        return '2.0.0';
    });
    Route::post('/login', 'AuthController@login');
    Route::post('/register', 'AuthController@register');
});

Route::group(['namespace' => 'Api', 'middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', 'AuthController@logout');

    //Profile
    Route::get('/me', 'UserController@show');

    //Activity
    Route::get('/activities', 'ActivityController@index');

    //Dashboard
    Route::get('/dashboard', 'DashboardController@index');

    //Clients
    Route::get('/clients', 'ClientController@index');
    Route::post('/clients', 'ClientController@store');
    Route::get('/clients/{client}', 'ClientController@show');
    Route::patch('/clients/{client}', 'ClientController@update');
    Route::delete('/clients/{client}', 'ClientController@destroy');
    Route::patch('/clients/{client}/restore', 'ClientController@restore')->name('clients.restore');
    Route::delete('/clients/{client}/forcedelete', 'ClientController@forceDelete')->name('clients.forcedelete');
    Route::get('/clients/{client}/activity', 'ClientController@activity')->name('clients.activity');
    Route::get('/clients/{client?}/projects', 'ProjectController@index');

    Route::get('/tasks', 'TaskController@index');

    Route::prefix('projects')->group(function () {
        //Projects
        Route::get('/', 'ProjectController@index');
        Route::post('/', 'ProjectController@store');
        Route::get('/{project}', 'ProjectController@show');
        Route::patch('/{project}', 'ProjectController@update');
        Route::delete('/{project}', 'ProjectController@destroy');
        Route::patch('/{project}/restore', 'ProjectController@restore')->name('projects.restore');
        Route::delete('/{project}/forcedelete', 'ProjectController@forceDelete')->name('projects.forcedelete');
        Route::get('/{project}/activity', 'ProjectController@activity')->name('projects.activity');
        Route::get('/{project?}/tasks', 'TaskController@index');

        //Tasks
        Route::post('/{project}/tasks', 'TaskController@store');
        Route::get('/{project}/tasks/{task}', 'TaskController@show');
        Route::patch('/{project}/tasks/{task}', 'TaskController@update');
        Route::delete('/{project}/tasks/{task}', 'TaskController@destroy');
        Route::patch('/{project}/tasks/{task}/restore', 'TaskController@restore')->name('tasks.restore');
        Route::delete('/{project}/tasks/{task}/forcedelete', 'TaskController@forceDelete')->name('tasks.forcedelete');
        Route::patch('/{project}/tasks/{task}/completed', 'TaskController@completed')->name('tasks.toggleCompleted');
        Route::patch('/{project}/tasks/{task}/billed', 'TaskController@billed')->name('tasks.toggleBilled');
        Route::get('/{project}/tasks/{task}/activity', 'TaskController@activity')->name('tasks.activity');
    });
});
