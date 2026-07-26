<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/register",
        summary: "Inscription d'un nouvel utilisateur (patient ou medecin)",
        tags: ["Authentification"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation", "role"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jean Dupont"),
                    new OA\Property(property: "email", type: "string", example: "patient@test.com"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "password123"),
                    new OA\Property(property: "role", type: "string", enum: ["patient", "medecin"], example: "patient"),
                    new OA\Property(property: "specialite", type: "string", example: "Generaliste", description: "Requis si role=medecin"),
                    new OA\Property(property: "tarif_consultation", type: "number", example: 25000, description: "Requis si role=medecin"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Utilisateur cree avec succes"),
            new OA\Response(response: 422, description: "Erreur de validation"),
        ]
    )]

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'specialite' => 'nullable|required_if:role,medecin|string|max:255',
            'tarif_consultation' => 'nullable|required_if:role,medecin|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->role === 'medecin') {
            $user->medecinProfile()->create([
                'specialite' => $request->specialite,
                'tarif_consultation' => $request->tarif_consultation,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('medecinProfile'),
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: "/login",
        summary: "Connexion d'un utilisateur existant",
        tags: ["Authentification"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", example: "patient@test.com"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Connexion reussie, token retourne"),
            new OA\Response(response: 401, description: "Identifiants invalides"),
        ]
    )]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    #[OA\Post(
        path: "/logout",
        summary: "Deconnexion (invalide le token courant)",
        tags: ["Authentification"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Deconnexion reussie"),
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Deconnecte avec succes.']);
    }

    #[OA\Get(
        path: "/me",
        summary: "Recuperer les infos de l'utilisateur connecte",
        tags: ["Authentification"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Infos de l'utilisateur"),
        ]
    )]
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'password' => Hash::make(str()->random(24)),
                'role' => 'patient',
                'google_id' => $googleUser->getId(),
            ]);
        } elseif (! $user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}