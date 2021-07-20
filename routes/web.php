<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;

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


//Rutas del controlador de usuario
Route::get('usuarios', [UserController::class, 'prueba']);
Route::post('usuarios/login', [UserController::class, 'login']);
Route::post('usuarios/registro', [UserController::class, 'registro']);
Route::put('usuarios/update', [UserController::class, 'update']);
Route::post('usuarios/upload', [UserController::class, 'upload'])->middleware('apiauth');
Route::get('usuarios/getimage/{filename}', [UserController::class, 'getimagen']);

//Rutas del controlador de Categorias
Route::resource('category', CategoryController::class);

//Rutas del controlador Posts
Route::resource('post', PostController::class);
Route::post('post/upload', [PostController::class, 'upload']);
Route::get('post/getimage/{filename}', [PostController::class, 'getImagen']);
Route::get('post/category/{id}', [PostController::class, 'getPostsByCategory']);
Route::get('post/user/{id}', [PostController::class, 'getPostsByUser']);

