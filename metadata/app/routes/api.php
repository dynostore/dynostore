<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureTokenIsValid;

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

Route::group(['prefix' => 'storage', 'middleware' => [EnsureTokenIsValid::class]], function(){
    Route::get('{tokenuser}/{keyfile}/exists', [FileController::class, 'exists']);
    Route::delete('{tokenuser}/{keyfile}', [FileController::class, 'delete']);
    Route::put('{tokenuser}/{tokencatalog}/{keyfile}', [FileController::class, 'push']);
    Route::get('{tokenuser}/{keyfile}', [FileController::class, 'pull']);
});

Route::post('servers/{tokenuser}', [ServerController::class, 'store']);
Route::get('servers/{tokenuser}', [ServerController::class, 'index']);
Route::get('servers/{tokenuser}/statistics', [ServerController::class, 'statistics']);