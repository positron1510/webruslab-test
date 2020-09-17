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

Route::get('/posts', 'PostController@getPosts')->name('posts');

Route::get('/post/{post_id}', 'PostController@getOnePost')->name('one_post')->where(['post_id'=>'^\d+$']);

Route::get('/post/add', 'PostController@addPost')->name('add_post');