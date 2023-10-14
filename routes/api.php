<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\NotificationController;
use Illuminate\Broadcasting\BroadcastController;
use App\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Broadcast;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('authenticate', [AuthenticationController::class, 'login']);
        Route::post('register', [AuthenticationController::class, 'register']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::put('updateProfile', [AuthenticationController::class, 'updateProfile']);
            Route::get('userProfile', [AuthenticationController::class, 'userProfile']);
            Route::get('enable2FA', [AuthenticationController::class, 'enableTwoFactorAuthentication']);
            Route::get('disable2FA', [AuthenticationController::class, 'disableTwoFactorAuthentication']);
            Route::post('verify2FA', [AuthenticationController::class, 'verifyTwoFactorSetup']);
            Route::post('savedeviceKey', [AuthenticationController::class, 'saveDeviceKey']);
            Route::post('testnotify', [NotificationController::class, 'testnotify']);
        });
    });

    Route::prefix('group')->middleware('auth:sanctum')->group(function () {
        Route::get('usergroups', [MessagesController::class, 'getUserGroups']);
        Route::post('createwithusers', [MessagesController::class, 'createGroupWithUsers']);
        Route::post('adduser', [MessagesController::class, 'addUserToGroup']);
        Route::post('messages', [MessagesController::class, 'groupMessages']);
    });
    Route::prefix('message')->middleware('auth:sanctum')->group(function () {
        Route::post('createMessageGroup', [MessagesController::class, 'createMessageGroup']);
        Route::post('createMessage', [MessagesController::class, 'createMessage']);
        Route::post('userMessage', [MessagesController::class, 'userMessage']);
    });
    Route::prefix('user')->middleware('auth:sanctum')->group(function () {
        Route::post('contacts', [MessagesController::class, 'getContacts']);
        Route::get('all', [MessagesController::class, 'allContacts']);
    });
    Route::post('testtwilioService', [AuthenticationController::class, 'testtwilioService']);

});
