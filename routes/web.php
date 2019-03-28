<?php

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


Route::get('/test', 'chatController@test');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::post('/new_convo',  'chatController@newConvo');

Route::post('/send_message',  'chatController@sendMessage');


Route::get("/my_private_subs", 'chatController@myPrivateSubs');

Route::get("/my_subscriptions", "chatController@getCurrentSubs");

ROute::post("/messages",   "chatController@getMessages");