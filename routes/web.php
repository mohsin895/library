<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Temporary test route
Route::get('/test-chapter/{id}', function($id) {
    return response()->json([
        'exists' => \App\Models\Chapter::where('id', $id)->exists()
    ]);
});