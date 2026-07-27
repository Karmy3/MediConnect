<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

const recherche = ref('')
const medecins = ref<any[]>([])
const medecinSelectionne = ref<any>(null)
const creneaux = ref<any[]>([])
const symptomes = ref('')
const erreur = ref('')
const chargementRecherche = ref(false)
const chargementCreneaux = ref(false)
const chargementReservation = ref(false)
const succes = ref(false)

function initiales(nom: string) {
  return nom ? nom.split(' ').map((n) => n[0]).join('').slice(0, 2).toUpperCase() : '?'
}

async function chercherMedecins() {
  erreur.value = ''
  chargementRecherche.value = true
  medecinSelectionne.value = null
  creneaux.value = []

  try {
    const response = await api.get('/medecins', {
      params: { recherche: recherche.value },
    })
    medecins.value = response.data
    if (!medecins.value.length) {
      erreur.value = recherche.value
        ? 'Aucun medecin trouve pour cette recherche.'
        : 'Aucun medecin disponible pour le moment.'
    }
  } catch (e: any) {
    erreur.value = 'Erreur lors de la recherche.'
  } finally {
    chargementRecherche.value = false
  }
}

function effacerRecherche() {
  recherche.value = ''
  chercherMedecins()
}

async function selectionnerMedecin(medecin: any) {
  medecinSelectionne.value = medecin
  creneaux.value = []
  chargementCreneaux.value = true

  try {
    const response = await api.get(`/medecins/${medecin.id}/creneaux-disponibles`)
    creneaux.value = response.data
    if (!creneaux.value.length) {
      erreur.value = 'Aucun creneau disponible pour ce medecin actuellement.'
    } else {
      erreur.value = ''
    }
  } finally {
    chargementCreneaux.value = false
  }
}

async function reserver(creneauId: number) {
  chargementReservation.value = true
  erreur.value = ''

  try {
    await api.post('/rendez-vous', {
      creneau_id: creneauId,
      symptomes_description: symptomes.value,
    })
    succes.value = true
    setTimeout(() => router.push('/patient/tableau-de-bord'), 1500)
  } catch (e: any) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la reservation.'
  } finally {
    chargementReservation.value = false
  }
}

onMounted(chercherMedecins)
</script>

<template>
  <div class="page">
    <header class="page-header">
      <router-link to="/patient/tableau-de-bord" class="retour">← Retour au tableau de bord</router-link>
    </header>

    <div class="page-content">
      <div class="intro">
        <h1>Prendre rendez-vous</h1>
        <p class="muted">Parcourez la liste des medecins ou cherchez par nom/specialite.</p>
      </div>

      <div class="card recherche-card">
        <div class="recherche">
          <div class="input-avec-effacer">
            <input
              v-model="recherche"
              type="text"
              placeholder="ex: Rakoto, Generaliste, Cardiologue..."
              @keyup.enter="chercherMedecins"
            />
            <button
              v-if="recherche"
              @click="effacerRecherche"
              class="btn-effacer"
              type="button"
              aria-label="Effacer la recherche"
            >
              ×
            </button>
          </div>
          <button @click="chercherMedecins" :disabled="chargementRecherche" class="btn-primary">
            {{ chargementRecherche ? 'Recherche...' : 'Chercher' }}
          </button>
        </div>
      </div>

      <p v-if="erreur" class="message erreur">{{ erreur }}</p>
      <p v-if="succes" class="message succes">Rendez-vous reserve avec succes ! Redirection...</p>

      <div v-if="chargementRecherche && !medecins.length" class="message">Chargement des medecins...</div>

      <div v-if="medecins.length && !medecinSelectionne" class="liste-medecins">
        <p class="section-label">
          {{ recherche ? 'Resultats de recherche' : `${medecins.length} medecin(s) disponible(s)` }}
        </p>
        <div
          v-for="m in medecins"
          :key="m.id"
          class="medecin-card"
          @click="selectionnerMedecin(m)"
        >
          <div class="avatar">{{ initiales(m.name) }}</div>
          <div class="medecin-card-info">
            <strong>Dr {{ m.name }}</strong>
            <span class="specialite">{{ m.specialite }}</span>
          </div>
          <span class="tarif">{{ m.tarif_consultation }} Ar</span>
        </div>
      </div>

      <div v-if="medecinSelectionne" class="card selection-card">
        <button @click="medecinSelectionne = null; creneaux = []" class="btn-retour-liste">
          ← Changer de medecin
        </button>

        <div class="medecin-header">
          <div class="avatar avatar-lg">{{ initiales(medecinSelectionne.name) }}</div>
          <div>
            <h3>Dr {{ medecinSelectionne.name }}</h3>
            <p class="specialite">{{ medecinSelectionne.specialite }} · {{ medecinSelectionne.tarif_consultation }} Ar</p>
          </div>
        </div>

        <label class="label-symptomes">
          Decrivez vos symptomes (optionnel)
          <textarea v-model="symptomes" rows="3" placeholder="Fievre, toux, depuis combien de temps..."></textarea>
        </label>

        <p v-if="chargementCreneaux" class="message">Chargement des creneaux...</p>

        <div v-else-if="creneaux.length" class="liste-creneaux">
          <p class="section-label">Creneaux disponibles</p>
          <div v-for="c in creneaux" :key="c.id" class="creneau">
            <span>{{ new Date(c.date_debut).toLocaleString('fr-FR') }}</span>
            <button
              @click="reserver(c.id)"
              :disabled="chargementReservation"
              class="btn-primary petit"
            >
              Reserver
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page {
  min-height: 100vh;
  background: var(--color-secondary);
}

.page-header {
  background: var(--color-white);
  padding: 18px 40px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.retour {
  color: var(--color-text-muted);
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
}

.page-content {
  max-width: 640px;
  margin: 0 auto;
  padding: 40px 20px;
}

.intro h1 {
  font-size: 26px;
}

.muted {
  color: var(--color-text-muted);
  font-size: 14px;
  margin: 6px 0 24px;
}

.card {
  background: var(--color-white);
  border-radius: var(--radius);
  padding: 24px;
  box-shadow: var(--shadow);
}

.recherche-card {
  margin-bottom: 16px;
}

.recherche {
  display: flex;
  gap: 12px;
}

.input-avec-effacer {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
}

.input-avec-effacer input {
  width: 100%;
  padding-right: 36px;
}

.btn-effacer {
  position: absolute;
  right: 8px;
  background: var(--color-secondary);
  border: none;
  color: var(--color-text-muted);
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
}

.btn-effacer:hover {
  background: var(--color-border);
  color: var(--color-text);
}

input, textarea {
  padding: 12px 14px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  font-size: 14px;
}

input:focus, textarea:focus {
  outline: none;
  border-color: var(--color-primary);
}

.btn-primary {
  background: var(--color-primary);
  color: var(--color-white);
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.btn-primary:hover:not(:disabled) {
  background: var(--color-primary-dark);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.petit {
  padding: 8px 16px;
  font-size: 13px;
}

.message {
  font-size: 14px;
  padding: 12px 14px;
  border-radius: 10px;
  margin-bottom: 16px;
  color: var(--color-text-muted);
}

.message.erreur {
  color: var(--color-danger);
  background: var(--color-danger-bg);
}

.message.succes {
  color: var(--color-success);
  background: var(--color-success-bg);
}

.liste-medecins {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 480px;
  overflow-y: auto;
}

.section-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--color-text-muted);
  font-weight: 700;
  margin: 0 0 4px;
}

.medecin-card {
  display: flex;
  align-items: center;
  gap: 14px;
  background: var(--color-white);
  padding: 16px;
  border-radius: var(--radius);
  cursor: pointer;
  box-shadow: var(--shadow);
  transition: transform 0.12s;
}

.medecin-card:hover {
  transform: translateY(-2px);
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  flex-shrink: 0;
}

.avatar-lg {
  width: 52px;
  height: 52px;
  font-size: 18px;
}

.medecin-card-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.specialite {
  color: var(--color-text-muted);
  font-size: 13px;
}

.tarif {
  color: var(--color-primary);
  font-weight: 700;
  font-size: 14px;
}

.selection-card {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 16px;
}

.btn-retour-liste {
  background: none;
  border: none;
  color: var(--color-primary);
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  padding: 0;
  text-align: left;
  align-self: flex-start;
}

.medecin-header {
  display: flex;
  align-items: center;
  gap: 14px;
}

.medecin-header h3 {
  font-size: 18px;
}

.label-symptomes {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

.liste-creneaux {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.creneau {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--color-secondary);
  padding: 14px 16px;
  border-radius: 10px;
  font-size: 14px;
}
</style>