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
    Route::prefix('projects')->group(function () {
        /*
        //Projects
        Route::get('/', 'ProjectController@index');
        Route::get('/create', 'ProjectController@create');
        Route::post('/', 'ProjectController@store');
        Route::get('/{project}', 'ProjectController@show');
        Route::get('/{project}/edit', 'ProjectController@edit');
        Route::patch('/{project}', 'ProjectController@update');
        Route::delete('/{project}', 'ProjectController@destroy');
        Route::patch('/{project}/restore', 'ProjectController@restore')->name('projects.restore');
        Route::delete('/{project}/forcedelete', 'ProjectController@forceDelete')->name('projects.forcedelete');
        */
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
