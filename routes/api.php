<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DdosController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordCheckController;
use App\Http\Controllers\PasswordGeneratorController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\FunctionalityController;
use App\Http\Controllers\CrawlerController;
use App\Http\Controllers\SpamMailController;
use App\Http\Controllers\FakeIdentityController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PhishingController;
use App\Http\Controllers\LogController;
use App\Http\Middleware\AdminMiddleware;

// Routes pour gérer l'auth via JWT
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api')->name('refresh');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api')->name('me');
});

// Routes protégées par le middleware auth:api 
Route::middleware(['auth:api'])->group(function () {
    Route::get('/users', [UserController::class, 'getUsers']);

    // Vérification si un mot de passe est sur la liste des plus courants 
    Route::post('/checkpassword', [PasswordCheckController::class, 'check'])->name('checkpassword');
    // Générateur de mot de passe sécurisé
    Route::post('/password/generate', [PasswordGeneratorController::class, 'generate'])->name('generate.password');

    // Routes pour l'administration des fonctionnalités des utilisateurs
    Route::post('/users/{user}/functionalities', [FunctionalityController::class, 'addFunctionality'])
        ->middleware(AdminMiddleware::class)
        ->name('addFunctionality');
    Route::delete('/users/{user}/functionalities/{functionality}', [FunctionalityController::class, 'removeFunctionality'])
        ->middleware(AdminMiddleware::class)
        ->name('removeFunctionality');

    // Routes pour les logs
    Route::get('/logs', [LogController::class, 'getAllLogs'])->middleware(AdminMiddleware::class);
    Route::get('/users/{user}/logs', [LogController::class, 'getUserLogs'])->middleware(AdminMiddleware::class);
    Route::get('/functionalities/{functionality}/logs', [LogController::class, 'getFunctionalityLogs'])->middleware(AdminMiddleware::class);


    // Route pour "ddos" :
    Route::post('/ddos', [DdosController::class, 'simulateDdos'])->name('simulate.ddos');

    // Outil de vérification d'existence d'adresse mail
    Route::get('/verify-email', [EmailVerificationController::class, 'verifyEmail'])->name('verify.email');

    // Spammer de mail (contenu + nombre d'envoi) 
    Route::post('/spam-email', [SpamMailController::class, 'sendEmail'])->name('send.email');

    // Crawler d'informations sur une personne (à partir d'un nom / prénom)
    Route::post('/crawl-person', [CrawlerController::class, 'crawlPerson']);

    // Récupérer tous les domaines & sous-domaines associés à un Nom De Domaine
    Route::post('/crawl-domains', [DomainController::class, 'crawlDomains']);

    // Image random de quelqu'un qui n'existe pas
    Route::get('/random-image', [ImageController::class, 'getRandomImage'])->name('random.image');

    // Génération d'identité fictive
    Route::get('/fake-identity', [FakeIdentityController::class, 'generateFakeIdentity'])->name('fake.identity');

    // Service de phishing (création d'une page web de phishing sur mesure, backé sur de l'IA)
    Route::post('/phishing', [PhishingController::class, 'createPhishing']);
    Route::post('/savephishing', [PhishingController::class, 'saveIdentifiants']);
});
