<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
 */
Route::get('/{album}/photos', 'FilesController@getAlbumPhotos');
Route::put('/photo/{photo}/{type}', 'FilesController@updatePhotoData');
Route::post('photo/order', 'FilesController@orderPhotos');
Route::delete('photo/{photo}', 'FilesController@destroyPhoto');
Route::post('/upload-files', 'FilesController@storeFiles');
