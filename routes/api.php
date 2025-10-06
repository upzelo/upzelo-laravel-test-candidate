<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

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

//Temporary register router to add user while testing in postman, presumably will be done in front end app
Route::post('register',[UserAuthController::class,'register']);
Route::post('login',[UserAuthController::class,'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user',  function (Request $request) {
        return $request->user();
    });

    
    Route::post('logout',[UserAuthController::class,'logout']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
});
