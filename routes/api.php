<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);



Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/products', [ProductController::class, 'store']);
Route::middleware('auth:sanctum')->post('/orders', [OrderController::class, 'store']);
Route::middleware('auth:sanctum')->get('/my-orders', [OrderController::class, 'index']);
Route::middleware('auth:sanctum')->post('/checkout/{order}', [OrderController::class, 'checkout']);
Route::post('/admin/login', [AdminController::class, 'login']);
Route::middleware(['auth:sanctum', 'admin'])->get('/admin/products', [AdminController::class, 'products']);
Route::middleware('auth:sanctum')->delete('/admin/products/{id}', [AdminController::class, 'deleteProduct']);

Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);


