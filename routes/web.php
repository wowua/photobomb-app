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

Route::prefix('api')->group(function () {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('refreshToken', 'AuthController@refreshToken');

    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('user', 'AuthController@getUser');

        Route::post('updateUser', 'UsersController@update');
        Route::get('userInfo/{userId?}', 'UsersController@info');

        Route::post('albums', 'AlbumsController@create');
        Route::get('albums', 'AlbumsController@view');

        Route::get('albums/{id}', 'AlbumsController@getById');
        Route::post('albums/{id}', 'AlbumsController@update');

        Route::delete('albums/{id}', 'AlbumsController@remove');

        Route::post('photos', 'PhotosController@uploadPhotos');
        Route::get('photos/recent/', 'PhotosController@getRecent');

        Route::post('photos/{photoId}', 'PhotosController@edit');
        Route::get('photos/{albumId}', 'PhotosController@view');
        Route::delete('photos/{photoId}', 'PhotosController@remove');

        Route::get('photo/{photoId}', 'PhotosController@getPhotoInfo');

        Route::post('/like', 'LikesController@add');
        Route::post('/dislike', 'LikesController@remove');

        Route::post('/comments', 'CommentsController@add');

        Route::get('/comments/{photoId}', 'CommentsController@get');
    });
});

Route::get('/', function () {
    return view('index');
});
