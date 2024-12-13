<?php

use App\Http\Controllers\TodoController;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/todos', [TodoController::class, 'index']); // List with filters & search
Route::post('/todos', [TodoController::class, 'store']); // Create
Route::put('/todos/{id}', [TodoController::class, 'update']); // Update
Route::delete('/todos/{id}', [TodoController::class, 'destroy']); // Delete
Route::get('/todos/deleted', [TodoController::class, 'deletedRecords']);
