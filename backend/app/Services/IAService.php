<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IAService
{
    public function analyserSymptomes(string $description): string
    {
        $response = Http::withToken(config('services.groq.key'))
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Tu es un assistant medical qui aide les medecins en pre-analysant '
                            .'les symptomes decrits par un patient avant une consultation. '
                            .'Fournis une synthese courte (3-5 lignes) : symptomes cles, '
                            .'gravite potentielle apparente, et points a verifier en priorite. '
                            .'Precise toujours que ceci ne remplace pas un diagnostic medical.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Symptomes decrits par le patient : {$description}",
                    ],
                ],
                'temperature' => 0.3,
                'max_tokens' => 300,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Erreur lors de l\'appel a l\'API IA : '.$response->body());
        }

        return $response->json('choices.0.message.content') ?? 'Analyse indisponible.';
    }
}