<?php

use App\Http\Controllers\Auth\AuthenticateController;
use App\Models\User;
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


Route::post('/login', [AuthenticateController::class, "login"])->name("login");
Route::post('/logout', [AuthenticateController::class, "destroy"])->middleware("auth:sanctum")->name("logout");

Route::group(["prefix" => "users"], function () {
    Route::post('/', [\App\Http\Controllers\UserController::class, "store"])->name("create_user");
    Route::group(["middleware" => "auth:sanctum"], function () {
        Route::get('/', [\App\Http\Controllers\UserController::class, "index"])->name("get_all_users");
        Route::get('/{user}', [\App\Http\Controllers\UserController::class, "show"])->name("get_user");
        Route::patch('/{user}', [\App\Http\Controllers\UserController::class, "update"])->name("update_user");
        Route::delete('/{user}', [\App\Http\Controllers\UserController::class, "destroy"])->name("delete_user");
    });
});

Route::group(["middleware" => 'auth:sanctum'], function () {
    Route::group(["prefix" => "teams"], function () {
        Route::get('/', [\App\Http\Controllers\TeamController::class, "index"])->name("get_all_teams");
        Route::post('/', [\App\Http\Controllers\TeamController::class, "store"])->name("create_team");
        Route::get('/{team}', [\App\Http\Controllers\TeamController::class, "show"])->name("get_team");
        Route::patch('/{team}', [\App\Http\Controllers\TeamController::class, "update"])->name("update_team");
        Route::delete('/{team}', [\App\Http\Controllers\TeamController::class, "destroy"])->name("delete_team");
        Route::get('/{team}/players', [\App\Http\Controllers\TeamController::class, "players"])->name("team_players");
    });


    Route::group(["prefix" => "players"], function () {
        Route::get('/', [\App\Http\Controllers\PlayerController::class, "index"])->name("get_all_players");
        Route::post('/', [\App\Http\Controllers\PlayerController::class, "store"])->name("create_player");
        Route::get('/{player}', [\App\Http\Controllers\PlayerController::class, "show"])->name("get_player");
        Route::patch('/{player}', [\App\Http\Controllers\PlayerController::class, "update"])->name("update_player");
        Route::delete('/{player}', [\App\Http\Controllers\PlayerController::class, "destroy"])->name("delete_player");
    });

    Route::group(["prefix" => "markets"], function () {
        Route::get('/', [\App\Http\Controllers\MarketController::class, "index"])->name("get_all_markets");
        Route::post('/', [\App\Http\Controllers\MarketController::class, "store"])->name("create_market");
        Route::get('/{market}', [\App\Http\Controllers\MarketController::class, "show"])->name("get_market");
        Route::patch('/{market}', [\App\Http\Controllers\MarketController::class, "update"])->name("update_market");
        Route::delete('/{market}', [\App\Http\Controllers\MarketController::class, "destroy"])->name("delete_market");
    });

});


Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    /** @var User $user */
    $user = $request->user();
    $user->load('team.players');
    return $user;
})->name("me");
