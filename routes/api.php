<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\AuthenticationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('authenticate', [AuthenticationController::class, 'login']);
    });

    Route::prefix('group')->middleware('auth:sanctum')->group(function () {
        Route::get('usergroups', [MessagesController::class, 'getUserGroups']);
        Route::post('createwithusers', [MessagesController::class, 'createGroupWithUsers']);
        Route::post('adduser', [MessagesController::class, 'addUserToGroup']);
        Route::post('messages', [MessagesController::class, 'groupMessages']);
    });
    Route::prefix('message')->middleware('auth:sanctum')->group(function () {
        Route::post('create', [MessagesController::class, 'createMessage']);
    });

});
