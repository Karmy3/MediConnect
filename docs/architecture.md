# Architecture de MediConnect

```mermaid
flowchart TB
    subgraph Client
        A[Frontend Vue 3 + TypeScript<br/>Port 5173]
    end

    subgraph Backend
        B[API Laravel 11<br/>Port 8000]
        C[Queue Worker<br/>Laravel Queue]
        D[Scheduler<br/>Rappels 24h avant RDV]
    end

    subgraph Base de données
        E[(PostgreSQL 16)]
    end

    subgraph Services tiers
        F[Stripe<br/>Paiement]
        G[Groq API<br/>Pre-analyse IA]
        H[Mailtrap<br/>Emails]
        I[Google OAuth<br/>Authentification]
    end

    A -- HTTP / JSON --> B
    B -- Eloquent ORM --> E
    B -- Dispatch jobs --> C
    C -- Lit / ecrit --> E
    D -- Dispatch jobs --> C
    B -- Paiement consultation --> F
    B -- Analyse symptomes --> G
    C -- Envoi email confirmation --> H
    B -- Connexion sociale --> I
```

## Description des composants

- **Frontend** : application Vue 3 (TypeScript), consomme l'API REST via Axios, gère l'état d'authentification avec Pinia et la navigation avec Vue Router.
- **API Laravel** : expose les routes REST (authentification, créneaux, rendez-vous, paiements), protégées par Sanctum (tokens JWT) et un middleware de rôles (patient/médecin/admin).
- **Queue Worker** : traite en arrière-plan l'envoi des emails transactionnels, pour ne pas bloquer les réponses de l'API.
- **Scheduler** : déclenche toutes les heures une commande qui vérifie les rendez-vous à venir dans 24h et programme les rappels.
- **PostgreSQL** : stocke toutes les données métier (utilisateurs, créneaux, rendez-vous, paiements).
- **Services tiers** : Stripe pour le paiement, Groq pour la pré-analyse IA des symptômes, Mailtrap pour les emails de test, Google OAuth pour la connexion sociale.