<?php

use App\Http\Controllers\TodoController;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/todos', [TodoController::class, 'index']); // List with filters & search todos
Route::post('/todos', [TodoController::class, 'store']); // Create todo
Route::put('/todos/{id}', [TodoController::class, 'update']); // Update todo
Route::delete('/todos/{id}', [TodoController::class, 'destroy']); // Delete todo
Route::get('/todos/deleted', [TodoController::class, 'deletedTodo']); // get deleted todos
Route::get("/todos/restore/{id}", [TodoController::class, 'restoreTodo']); //restore todos
Route::delete("/todos/delete-perm/{id}", [TodoController::class, 'deletePermanently']); // delete todo permanently
