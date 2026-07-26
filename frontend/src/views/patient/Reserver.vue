<script setup lang="ts">
import { ref } from 'vue'
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

async function chercherMedecins() {
  erreur.value = ''
  chargementRecherche.value = true
  medecins.value = []
  medecinSelectionne.value = null
  creneaux.value = []

  try {
    const response = await api.get('/medecins', {
      params: { recherche: recherche.value },
    })
    medecins.value = response.data
    if (!medecins.value.length) {
      erreur.value = 'Aucun medecin trouve.'
    }
  } catch (e: any) {
    erreur.value = 'Erreur lors de la recherche.'
  } finally {
    chargementRecherche.value = false
  }
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
</script>

<template>
  <div class="page">
    <div class="card">
      <router-link to="/patient/tableau-de-bord" class="retour">← Retour</router-link>
      <h1>Prendre rendez-vous</h1>

      <div class="recherche">
        <label>
          Nom ou specialite du medecin
          <input
            v-model="recherche"
            type="text"
            placeholder="ex: Rakoto, Generaliste, Cardiologue..."
            @keyup.enter="chercherMedecins"
          />
        </label>
        <button @click="chercherMedecins" :disabled="chargementRecherche" class="btn-principal">
          {{ chargementRecherche ? 'Recherche...' : 'Chercher' }}
        </button>
      </div>

      <p v-if="erreur" class="erreur">{{ erreur }}</p>
      <p v-if="succes" class="succes">Rendez-vous reserve avec succes !</p>

      <div v-if="medecins.length && !medecinSelectionne" class="section">
        <h3>Medecins trouves</h3>
        <div class="liste-medecins">
          <div
            v-for="m in medecins"
            :key="m.id"
            class="medecin-card"
            @click="selectionnerMedecin(m)"
          >
            <strong>Dr {{ m.name }}</strong>
            <span>{{ m.specialite }}</span>
            <span class="tarif">{{ m.tarif_consultation }} Ar</span>
          </div>
        </div>
      </div>

      <div v-if="medecinSelectionne" class="section">
        <button @click="medecinSelectionne = null; creneaux = []" class="btn-retour-liste">
          ← Changer de medecin
        </button>
        <h3>Dr {{ medecinSelectionne.name }} — {{ medecinSelectionne.specialite }}</h3>

        <label class="label-symptomes">
          Decrivez vos symptomes (optionnel)
          <textarea v-model="symptomes" rows="3" placeholder="Fievre, toux, depuis combien de temps..."></textarea>
        </label>

        <p v-if="chargementCreneaux">Chargement des creneaux...</p>

        <div v-else-if="creneaux.length" class="liste-creneaux">
          <div v-for="c in creneaux" :key="c.id" class="creneau">
            <span>{{ new Date(c.date_debut).toLocaleString('fr-FR') }}</span>
            <button
              @click="reserver(c.id)"
              :disabled="chargementReservation"
              class="btn-principal petit"
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
.page { min-height: 100vh; background: #f4f6f8; padding: 32px; }
.card { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.retour { color: #6b7280; text-decoration: none; font-size: 14px; }
h1 { margin: 12px 0 24px; }
.recherche { display: flex; gap: 12px; align-items: flex-end; margin-bottom: 20px; }
label { display: flex; flex-direction: column; gap: 6px; font-size: 14px; color: #374151; flex: 1; }
input, textarea { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; font-family: inherit; }
.btn-principal { background: #2563eb; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-size: 14px; white-space: nowrap; }
.btn-principal:disabled { opacity: 0.6; cursor: not-allowed; }
.petit { padding: 6px 14px; font-size: 13px; }
.erreur { color: #dc2626; font-size: 14px; }
.succes { color: #059669; font-size: 14px; }
.section { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
.label-symptomes { margin: 16px 0; }
.liste-medecins { display: flex; flex-direction: column; gap: 10px; }
.medecin-card { display: flex; flex-direction: column; gap: 4px; background: #f9fafb; padding: 14px 16px; border-radius: 8px; cursor: pointer; transition: background 0.15s; }
.medecin-card:hover { background: #eff6ff; }
.medecin-card .tarif { color: #2563eb; font-size: 13px; font-weight: 500; }
.medecin-card span { font-size: 13px; color: #6b7280; }
.btn-retour-liste { background: none; border: none; color: #2563eb; cursor: pointer; font-size: 13px; margin-bottom: 12px; padding: 0; }
.liste-creneaux { display: flex; flex-direction: column; gap: 10px; }
.creneau { display: flex; justify-content: space-between; align-items: center; background: #f9fafb; padding: 12px 16px; border-radius: 8px; }
</style>