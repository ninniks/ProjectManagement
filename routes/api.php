<?php

use App\Enum\ProjectStatusEnum;
use App\Enum\TaskStatusEnum;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\UserController;
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
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth')->prefix('projects')->group(function (){

   Route::get('/',[ProjectController::class, 'index']);
   Route::get('/{project_id}', [ProjectController::class, 'show']);
   Route::post('/', [ProjectController::class, 'store']);
   Route::patch('/{project_id}', [ProjectController::class, 'update']);
   Route::patch('/{project_id}/{status}', [ProjectController::class, 'updateStatus'])
       ->where('status', ProjectStatusEnum::Open->value.'|'.ProjectStatusEnum::Closed->value);

   Route::get('/{project_id}/tasks', [ProjectTaskController::class, 'index']);
   Route::get('/{project_id}/tasks/{task_id}', [ProjectTaskController::class, 'show']);
   Route::post('/{project_id}/tasks', [ProjectTaskController::class, 'store']);
   Route::patch('/{project_id}/tasks/{task_id}', [ProjectTaskController::class, 'update']);
   /** @uses ProjectController::updateTaskStatus */
   Route::patch('/{project_id}/tasks/{task_id}/{status}', [ProjectTaskController::class, 'updateTaskStatus'])
       ->where('status',
           TaskStatusEnum::Open->value.'|'.
           TaskStatusEnum::Blocked->value.'|'.TaskStatusEnum::Closed->value);
});
