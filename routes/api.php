<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function(){
    Route::prefix('v1')->group(function(){
        Route::apiResource('album', \App\Http\Controllers\V1\AlbumController::class);
        Route::get('images', [\App\Http\Controllers\V1\ImageController::class, 'index',]);
        Route::get('images/by-album/{album}', [\App\Http\Controllers\V1\ImageController::class, 'byAlbum',]);
        Route::get('images/{images}', [\App\Http\Controllers\V1\ImageController::class, 'show',]);
        Route::post('images/resize', [\App\Http\Controllers\V1\ImageController::class, 'resize',]);
        Route::delete('images/{images}', [\App\Http\Controllers\V1\ImageController::class, 'destroy',]);
    });
});



