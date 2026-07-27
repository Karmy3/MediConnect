<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0fdfa;
            margin: 0;
            padding: 0;
            color: #134e4a;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(13, 148, 136, 0.08);
        }
        .header {
            background: linear-gradient(160deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            padding: 32px;
            text-align: center;
        }
        .header .brand {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .body {
            padding: 32px;
        }
        .body p {
            margin: 0 0 16px;
            line-height: 1.6;
        }
        .details {
            background-color: #f0fdfa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .details p {
            margin: 8px 0;
            font-size: 14px;
        }
        .details strong {
            color: #0d9488;
        }
        .footer {
            padding: 20px 32px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">+ MediConnect</div>
            <h1>Rendez-vous confirme</h1>
        </div>
        <div class="body">
            <p>Bonjour {{ $rendezVous->patient->name }},</p>
            <p>Bonne nouvelle : votre rendez-vous a ete confirme par votre medecin. Voici le recapitulatif de votre consultation :</p>

            <div class="details">
                <p><strong>Medecin :</strong> Dr {{ $rendezVous->creneau->medecinProfile->user->name }}</p>
                <p><strong>Specialite :</strong> {{ $rendezVous->creneau->medecinProfile->specialite }}</p>
                <p><strong>Date :</strong> {{ $rendezVous->creneau->date_debut->format('d/m/Y a H:i') }}</p>
            </div>

            <p>Merci de vous connecter quelques minutes avant l'heure du rendez-vous. A bientot sur MediConnect.</p>
        </div>
        <div class="footer">
            MediConnect - Plateforme de telemedecine<br>
            Ceci est un email automatique, merci de ne pas y repondre.
        </div>
    </div>
</body>
</html>