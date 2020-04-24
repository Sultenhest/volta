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
    Route::get('/clients', 'ClientController@index');
    Route::get('/clients/create', 'ClientController@create');
    Route::post('/clients', 'ClientController@store');
    Route::get('/clients/{client}', 'ClientController@show');
    Route::get('/clients/{client}/edit', 'ClientController@edit');
    Route::patch('/clients/{client}', 'ClientController@update');
    Route::delete('/clients/{client}', 'ClientController@destroy');
    Route::patch('/clients/{client}/restore', 'ClientController@restore')->name('clients.restore');
    Route::delete('/clients/{client}/forcedelete', 'ClientController@forceDelete')->name('clients.forcedelete');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
