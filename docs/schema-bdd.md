# Schéma de la base de données — MediConnect

```mermaid
erDiagram
    USERS ||--o| MEDECIN_PROFILES : "a un profil (si medecin)"
    USERS ||--o{ RENDEZ_VOUS : "reserve (patient)"
    MEDECIN_PROFILES ||--o{ CRENEAUX : "cree"
    CRENEAUX ||--o| RENDEZ_VOUS : "est reserve dans"
    RENDEZ_VOUS ||--o| PAIEMENTS : "genere"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "patient, medecin, admin"
        string google_id
        timestamp created_at
    }

    MEDECIN_PROFILES {
        bigint id PK
        bigint user_id FK
        string specialite
        decimal tarif_consultation
        text bio
    }

    CRENEAUX {
        bigint id PK
        bigint medecin_profile_id FK
        datetime date_debut
        datetime date_fin
        enum statut "disponible, reserve"
    }

    RENDEZ_VOUS {
        bigint id PK
        bigint patient_id FK
        bigint creneau_id FK
        enum statut "en_attente, paye, confirme, termine, annule"
        text symptomes_description
        text analyse_ia
    }

    PAIEMENTS {
        bigint id PK
        bigint rendez_vous_id FK
        decimal montant
        string stripe_payment_id
        enum statut "en_attente, reussi, echoue, rembourse"
    }
```

## Relations principales

- Un **utilisateur** (`users`) peut être patient, médecin ou admin selon le champ `role`.
- Un médecin possède un **profil médecin** (`medecin_profiles`) contenant sa spécialité et son tarif.
- Un profil médecin crée plusieurs **créneaux** (`creneaux`) de disponibilité.
- Un patient réserve un créneau, ce qui crée un **rendez-vous** (`rendez_vous`), qui passe par les statuts `en_attente` → `paye` → `confirme`.
- Un rendez-vous payé génère un enregistrement dans **paiements** (`paiements`), lié à Stripe via `stripe_payment_id`.