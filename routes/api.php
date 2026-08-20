<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\GameHistoryController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\RoundController;
use App\Http\Controllers\WordSubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ranking
Route::get('/ranking/weekly', [RankingController::class, 'weekly']);

// Game history (requires authenticated user)
Route::get('/games/history', [GameHistoryController::class, 'index'])
    ->middleware('auth:sanctum');

// Game management (no player auth required)
Route::post('/games', [GameController::class, 'store']);
Route::get('/games/{code}', [GameController::class, 'show']);
Route::post('/games/{code}/join', [GameController::class, 'join']);

// Round state (no player auth required)
Route::get('/games/{code}/round', [RoundController::class, 'show']);

// Actions requiring player authentication
Route::middleware('resolve.player')->group(function () {
    Route::post('/games/{code}/start-round', [RoundController::class, 'start']);
    Route::post('/games/{code}/submit-word', [WordSubmissionController::class, 'store'])
        ->middleware('throttle:word-submission');
    Route::post('/games/{code}/play-again', [GameController::class, 'playAgain']);
});
