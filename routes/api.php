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
   Route::post('/login', 'AuthController@login');
   Route::post('/register', 'AuthController@register'); 
});

Route::group(['namespace' => 'Api', 'middleware' => 'auth:api'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', 'AuthController@logout');

    //Clients
    Route::get('/clients', 'ClientController@index');
    Route::post('/clients', 'ClientController@store');
    Route::get('/clients/{client}', 'ClientController@show');
    Route::patch('/clients/{client}', 'ClientController@update');
    Route::delete('/clients/{client}', 'ClientController@destroy');
    Route::patch('/clients/{client}/restore', 'ClientController@restore')->name('clients.restore');
    Route::delete('/clients/{client}/forcedelete', 'ClientController@forceDelete')->name('clients.forcedelete');

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

        //Tasks
        Route::post('/{project}/tasks', 'TaskController@store');
        Route::get('/{project}/tasks/{task}', 'TaskController@show');
        Route::patch('/{project}/tasks/{task}', 'TaskController@update');
        Route::delete('/{project}/tasks/{task}', 'TaskController@destroy');
        Route::patch('/{project}/tasks/{task}/restore', 'TaskController@restore')->name('tasks.restore');
        Route::delete('/{project}/tasks/{task}/forcedelete', 'TaskController@forceDelete')->name('tasks.forcedelete');
    });
});
