<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "MediConnect API",
    description: "API de la plateforme de telemedecine MediConnect - L3 Informatique 2025-2026"
)]
#[OA\Server(
    url: "http://127.0.0.1:8000/api",
    description: "Serveur de developpement local"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
abstract class Controller
{
    //
}