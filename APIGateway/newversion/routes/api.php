<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PubSubController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::prefix('auth')->group(function () {
    Route::post('organization', [AuthController::class, 'createOrganization']);
    Route::get('organization/{acronym}/{name}', [AuthController::class, 'getOrganization']);
    Route::post('user', [AuthController::class, 'createUser']);
    Route::get('user/{tokenuser}', [AuthController::class, 'getUser']);
});

Route::group(['prefix' => 'pubsub', 'middleware' => [EnsureTokenIsValid::class]], function(){
    Route::put('{tokenuser}/catalog/{catalogname}', [PubSubController::class, 'createCatalog']);
    Route::get('{tokenuser}/catalog/{tokencatalog}', [PubSubController::class, 'getCatalog']);
    Route::delete('{tokenuser}/catalog/{tokencatalog}', [PubSubController::class, 'deleteCatalog']);
});

Route::group(['prefix' => 'storage', 'middleware' => [EnsureTokenIsValid::class]], function(){
    Route::put('{tokenuser}/{catalog}/{keyobject}', [StorageController::class, 'push']);
    Route::get('{tokenuser}/{keyobject}', [StorageController::class, 'pull']);
    Route::delete('{tokenuser}/{keyobject}', [StorageController::class, 'delete']);
    Route::get('{tokenuser}/{keyobject}/exists', [StorageController::class, 'exists']);
});