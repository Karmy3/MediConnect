<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CreneauController;
use App\Http\Controllers\Api\RendezVousController;
use App\Http\Controllers\Api\PaiementController;
use App\Http\Controllers\Api\MedecinController;

Route::middleware('throttle:60,1')->group(function () {

    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::middleware('role:admin')->group(function () {
            // routes reservees a l'admin, a completer plus tard
        });

        Route::middleware('role:medecin')->group(function () {
            Route::post('/creneaux', [CreneauController::class, 'store']);
            Route::get('/mes-creneaux', [CreneauController::class, 'mesCreneaux']);
            Route::delete('/creneaux/{creneau}', [CreneauController::class, 'destroy']);
            Route::get('/rendez-vous-medecin', [RendezVousController::class, 'rendezVousMedecin']);
            Route::patch('/rendez-vous/{rendezVous}/confirmer', [RendezVousController::class, 'confirmer']);
            Route::post('/rendez-vous/{rendezVous}/analyser-ia', [RendezVousController::class, 'analyserSymptomes']);
        });

        Route::middleware('role:patient')->group(function () {
            Route::post('/rendez-vous', [RendezVousController::class, 'store']);
            Route::get('/mes-rendez-vous', [RendezVousController::class, 'mesRendezVous']);
            Route::post('/rendez-vous/{rendezVous}/payer', [PaiementController::class, 'payer']);
            Route::get('/rendez-vous/{rendezVous}/paiement', [PaiementController::class, 'show']);
        });
    });

    Route::get('/medecins/{medecinProfileId}/creneaux-disponibles', [CreneauController::class, 'creneauxDisponibles']);
    Route::patch('/rendez-vous/{rendezVous}/annuler', [RendezVousController::class, 'annuler']);

    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

    Route::get('/medecins', [MedecinController::class, 'index']);

});