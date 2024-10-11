<?php
 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordCheckController;
use App\Http\Controllers\FunctionalityController;
use App\Http\Middleware\AdminMiddleware;
 
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::post('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});



// Routes protégées par le middleware auth:api et admin
Route::middleware(['auth:api'])->group(function () {
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/checkpassword', [PasswordCheckController::class, 'check'])->name('checkpassword');

    // Routes pour l'administration des fonctionnalités des utilisateurs
    Route::post('/users/functionalities/add', [FunctionalityController::class, 'addFunctionality'])
         ->middleware(AdminMiddleware::class)
         ->name('addFunctionality');
         
    Route::post('/users/functionalities/remove', [FunctionalityController::class, 'removeFunctionality'])
         ->middleware(AdminMiddleware::class)
         ->name('removeFunctionality');

    Route::get('/logs', [LogController::class, 'getAllLogs'])->middleware(AdminMiddleware::class);
    Route::get('/users/{user}/logs', [LogController::class, 'getUserLogs'])->middleware(AdminMiddleware::class);
    Route::get('/functionalities/{functionality}/logs', [LogController::class, 'getFunctionalityLogs'])->middleware(AdminMiddleware::class);
});
