# MediConnect — Plateforme de télémédecine

Application client-serveur permettant à des patients de rechercher un médecin, prendre rendez-vous, payer leur consultation en ligne et recevoir leurs confirmations par email. Projet final — L3 Informatique 2025-2026.

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | Laravel 11 (PHP 8.4) — API REST |
| Authentification | Laravel Sanctum (JWT) + Socialite (OAuth Google) |
| Frontend | Vue 3 + TypeScript + Vite + Pinia + Vue Router |
| Base de données | PostgreSQL 16 |
| Paiement | Stripe (mode test) |
| IA | Groq (openai/gpt-oss-20b) — pré-analyse des symptômes |
| Email | Mailtrap (dev) |
| Tâches asynchrones | Laravel Queue (driver database) |
| Conteneurisation | Docker + docker-compose |
| CI/CD | GitHub Actions |
| Documentation API | Scribe + L5-Swagger (OpenAPI 3.0) |

## Prérequis

- Docker Desktop installé et lancé

## Installation et lancement

```bash
git clone <url-du-depot>
cd mediconnect
```

Copiez le fichier d'environnement et remplissez vos propres clés (Stripe, Google OAuth, Groq, Mailtrap) :

```bash
cp backend/.env.example backend/.env
```

Lancez toute la stack :

```bash
docker compose up --build
```

Une fois les conteneurs démarrés, générez la clé d'application et migrez la base de données :

```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

## Accès à l'application

- **Frontend** : http://localhost:5173
- **API** : http://localhost:8000/api
- **Documentation Swagger** : http://localhost:8000/api/documentation
- **Documentation Scribe** : http://localhost:8000/docs

## Comptes de test

Créez ces comptes via la page d'inscription (`/inscription`), ou directement en base :

| Rôle | Email | Mot de passe |
|---|---|---|
| Patient | patient@test.com | password123 |
| Médecin | medecin@test.com | password123 |

Pour créer rapidement des médecins de test en base :

```bash
docker compose exec backend php artisan tinker
```

```php
$m = \App\Models\User::create(['name' => 'Rakoto Jean', 'email' => 'rakoto@test.com', 'password' => bcrypt('password123'), 'role' => 'medecin']);
$m->medecinProfile()->create(['specialite' => 'Generaliste', 'tarif_consultation' => 20000]);
```

## Fonctionnalités principales

- Inscription / connexion (patient ou médecin), avec gestion des rôles
- Connexion via Google OAuth
- Recherche de médecin par nom ou spécialité
- Création de créneaux de disponibilité (médecin)
- Réservation de rendez-vous (patient), avec verrou anti-double-réservation
- Paiement de la consultation via Stripe
- Confirmation du rendez-vous par le médecin (uniquement après paiement)
- Pré-analyse IA des symptômes décrits par le patient (Groq)
- Email de confirmation de rendez-vous (Mailtrap), envoyé en file d'attente
- Rappel automatique programmé 24h avant le rendez-vous (tâche planifiée)

## Tester l'API avec Postman

Une collection Postman est disponible dans `docs/mediconnect.postman_collection.json` (ou générable via `http://localhost:8000/docs.postman`).

## Architecture

Voir `docs/architecture.md` pour le schéma client-serveur et `docs/schema-bdd.md` pour le modèle relationnel de la base de données.

## Sécurité

- Validation systématique des entrées (Laravel Validator)
- Requêtes préparées (Eloquent ORM, protection injection SQL native)
- Rate limiting : 5 requêtes/minute sur `/register` et `/login`, 60/minute sur le reste de l'API
- En-têtes de sécurité HTTP (X-Content-Type-Options, X-Frame-Options, etc.)
- Secrets exclusivement en variables d'environnement (`.env`, jamais commité)

## CI/CD

Le pipeline (`.github/workflows/ci.yml`) s'exécute à chaque push sur `main` :
- Installation des dépendances backend (Composer) et frontend (npm)
- Build de production du frontend
- Vérification que les images Docker backend et frontend se construisent correctement