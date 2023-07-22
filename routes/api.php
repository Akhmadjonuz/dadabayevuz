<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\TelegramController;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/test', function () {
    return 1;
});


Route::post('telegram/webhook', [TelegramController::class, 'handle']);

Route::get('/images/{filename}', [ImageController::class, 'show'])->name('image.show');

Route::get('/all', function () {
    return Post::all();
});
