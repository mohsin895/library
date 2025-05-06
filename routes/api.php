<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\BookshelfController;
use App\Http\Controllers\API\ChapterController;
use App\Http\Controllers\API\PageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('bookshelves', BookshelfController::class);
    Route::apiResource('bookshelves.books', BookController::class);
    Route::apiResource('books.chapters', ChapterController::class);
    Route::apiResource('chapters.pages', PageController::class);



    // Search books
    Route::get('/books/search', [BookController::class, 'search']);

    // Get full chapter content
    Route::get('/chapters/{chapter}/content', [ChapterController::class, 'fullContent']);
});

// Token login/register routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

