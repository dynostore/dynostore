<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\ServerController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('files/{tokenuser}/exists/{keyfile}', [FileController::class, 'exists']);
Route::delete('files/{tokenuser}/delete/{keyfile}', [FileController::class, 'delete']);
Route::post('files/push', [FileController::class, 'push']);
Route::post('files/pull', [FileController::class, 'pull']);

Route::post('servers/{tokenuser}', [ServerController::class, 'store']);
Route::get('servers/{tokenuser}', [ServerController::class, 'index']);
Route::get('servers/{tokenuser}/statistics', [ServerController::class, 'statistics']);