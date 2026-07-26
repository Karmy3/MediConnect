<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f59e0b; color: #ffffff; padding: 24px; text-align: center; }
        .body { padding: 24px; color: #1f2937; }
        .details { background-color: #f4f6f8; border-radius: 6px; padding: 16px; margin: 16px 0; }
        .details p { margin: 6px 0; }
        .footer { padding: 16px 24px; font-size: 12px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rappel de rendez-vous</h1>
        </div>
        <div class="body">
            <p>Bonjour {{ $rendezVous->patient->name }},</p>
            <p>Nous vous rappelons que vous avez un rendez-vous demain sur MediConnect :</p>

            <div class="details">
                <p><strong>Medecin :</strong> {{ $rendezVous->creneau->medecinProfile->user->name }}</p>
                <p><strong>Date :</strong> {{ $rendezVous->creneau->date_debut->format('d/m/Y a H:i') }}</p>
            </div>

            <p>Pensez a vous connecter quelques minutes avant l'heure prevue.</p>
        </div>
        <div class="footer">
            MediConnect - Plateforme de telemedecine - Ceci est un email automatique, merci de ne pas y repondre.
        </div>
    </div>
</body>
</html>