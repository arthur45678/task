<?php

use App\Http\Controllers\VideoCallController;
use App\Http\Controllers\AgoraTokenController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Video Call Routes
Route::get('/', [VideoCallController::class, 'index'])->name('home');
Route::post('/room/create', [VideoCallController::class, 'createRoom'])->name('video-call.create');
Route::get('/room/{roomId}', [VideoCallController::class, 'room'])->name('video-call.room');

// Agora Token Routes
Route::get('/agora/token/{channelName}', [AgoraTokenController::class, 'generateToken'])->name('agora.token');