<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['middleware' => 'auth'], function () {
    //Clients
    Route::get('/clients', 'ClientController@index');
    Route::get('/clients/create', 'ClientController@create');
    Route::post('/clients', 'ClientController@store');
    Route::get('/clients/{client}', 'ClientController@show');
    Route::get('/clients/{client}/edit', 'ClientController@edit');
    Route::patch('/clients/{client}', 'ClientController@update');
    Route::delete('/clients/{client}', 'ClientController@destroy');
    Route::patch('/clients/{client}/restore', 'ClientController@restore')->name('clients.restore');
    Route::delete('/clients/{client}/forcedelete', 'ClientController@forceDelete')->name('clients.forcedelete');

    //Projects
    Route::get('/projects', 'ProjectController@index');
    Route::get('/projects/create', 'ProjectController@create');
    Route::post('/projects', 'ProjectController@store');
    Route::get('/projects/{project}', 'ProjectController@show');
    Route::get('/projects/{project}/edit', 'ProjectController@edit');
    Route::patch('/projects/{project}', 'ProjectController@update');
    Route::delete('/projects/{project}', 'ProjectController@destroy');
    Route::patch('/projects/{project}/restore', 'ProjectController@restore')->name('projects.restore');
    Route::delete('/projects/{project}/forcedelete', 'ProjectController@forceDelete')->name('projects.forcedelete');

    Route::prefix('projects')->group(function () {

        //Tasks
        Route::get('/{project}/tasks', 'TaskController@index');
        Route::get('/{project}/tasks/create', 'TaskController@create');
        Route::post('/{project}/tasks', 'TaskController@store');
        Route::get('/{project}/tasks/{task}', 'TaskController@show');
        Route::get('/{project}/tasks/{task}/edit', 'TaskController@edit');
        Route::patch('/{project}/tasks/{task}', 'TaskController@update');
        Route::delete('/{project}/tasks/{task}', 'TaskController@destroy');
        Route::patch('/{project}/tasks/{task}/restore', 'TaskController@restore')->name('tasks.restore');
        Route::delete('/{project}/tasks/{task}/forcedelete', 'TaskController@forceDelete')->name('tasks.forcedelete');
    });
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
