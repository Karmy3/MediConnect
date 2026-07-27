<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import ConfirmModal from '../../components/ConfirmModal.vue'
import Toast from '../../components/Toast.vue'

const creneaux = ref<any[]>([])
const chargement = ref(true)
const erreur = ref('')

const dateDebut = ref('')
const dateFin = ref('')

const creneauASupprimer = ref<number | null>(null)
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null)

function afficherToast(message: string, type: 'success' | 'error') {
  toast.value = { message, type }
  setTimeout(() => (toast.value = null), 3500)
}

async function chargerCreneaux() {
  chargement.value = true
  try {
    const response = await api.get('/mes-creneaux')
    creneaux.value = response.data
  } finally {
    chargement.value = false
  }
}

async function creerCreneau() {
  erreur.value = ''
  try {
    await api.post('/creneaux', {
      date_debut: dateDebut.value.replace('T', ' ') + ':00',
      date_fin: dateFin.value.replace('T', ' ') + ':00',
    })
    dateDebut.value = ''
    dateFin.value = ''
    await chargerCreneaux()
    afficherToast('Creneau ajoute avec succes.', 'success')
  } catch (e: any) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la creation du creneau.'
  }
}

function demanderSuppression(creneauId: number) {
  creneauASupprimer.value = creneauId
}

async function confirmerSuppression() {
  if (!creneauASupprimer.value) return
  try {
    await api.delete(`/creneaux/${creneauASupprimer.value}`)
    await chargerCreneaux()
    afficherToast('Creneau supprime.', 'success')
  } catch (e: any) {
    afficherToast(e.response?.data?.message || 'Erreur lors de la suppression.', 'error')
  } finally {
    creneauASupprimer.value = null
  }
}

onMounted(chargerCreneaux)
</script>

<template>
  <div class="page">
    <header class="page-header">
      <router-link to="/medecin/tableau-de-bord" class="retour">← Retour au tableau de bord</router-link>
    </header>

    <div class="page-content">
      <div class="intro">
        <h1>Mes creneaux de disponibilite</h1>
        <p class="muted">Ajoutez des creneaux pour que vos patients puissent prendre rendez-vous.</p>
      </div>

      <div class="card">
        <p class="section-label">Nouveau creneau</p>
        <div class="formulaire">
          <label>
            Debut
            <input v-model="dateDebut" type="datetime-local" />
          </label>
          <label>
            Fin
            <input v-model="dateFin" type="datetime-local" />
          </label>
          <button @click="creerCreneau" class="btn-primary">Ajouter</button>
        </div>
        <p v-if="erreur" class="message erreur">{{ erreur }}</p>
      </div>

      <div v-if="chargement" class="etat">Chargement...</div>
      <div v-else-if="!creneaux.length" class="etat-vide">
        <div class="etat-vide-icon">🗓️</div>
        <h3>Aucun creneau cree pour le moment</h3>
        <p class="muted">Utilisez le formulaire ci-dessus pour ajouter votre premiere disponibilite.</p>
      </div>

      <div v-else class="liste">
        <p class="section-label">Creneaux existants</p>
        <div v-for="c in creneaux" :key="c.id" class="creneau">
          <div class="creneau-info">
            <span>{{ new Date(c.date_debut).toLocaleString('fr-FR') }}</span>
            <span class="badge" :class="'badge-' + c.statut">{{ c.statut }}</span>
          </div>
          <button
            v-if="c.statut === 'disponible'"
            @click="demanderSuppression(c.id)"
            class="btn-danger petit"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>

    <ConfirmModal
      v-if="creneauASupprimer"
      titre="Supprimer ce creneau ?"
      message="Cette action est definitive et ne peut pas etre annulee."
      @confirm="confirmerSuppression"
      @cancel="creneauASupprimer = null"
    />

    <Toast v-if="toast" :message="toast.message" :type="toast.type" />
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
  margin-bottom: 20px;
}

.section-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--color-text-muted);
  font-weight: 700;
  margin: 0 0 14px;
}

.formulaire {
  display: flex;
  gap: 12px;
  align-items: flex-end;
  flex-wrap: wrap;
}

label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text);
}

input {
  padding: 12px 14px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  font-size: 14px;
}

input:focus {
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
}

.btn-primary:hover {
  background: var(--color-primary-dark);
}

.btn-danger {
  background: var(--color-danger-bg);
  color: var(--color-danger);
  border: none;
  padding: 9px 16px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
}

.petit {
  padding: 8px 14px;
  font-size: 13px;
}

.message {
  font-size: 14px;
  padding: 12px 14px;
  border-radius: 10px;
  margin-top: 14px;
}

.message.erreur {
  color: var(--color-danger);
  background: var(--color-danger-bg);
}

.etat {
  color: var(--color-text-muted);
  text-align: center;
  padding: 40px 0;
}

.etat-vide {
  text-align: center;
  padding: 50px 20px;
  background: var(--color-white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.etat-vide-icon {
  font-size: 36px;
  margin-bottom: 12px;
}

.etat-vide h3 {
  margin-bottom: 6px;
}

.liste {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.creneau {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--color-white);
  padding: 16px;
  border-radius: 12px;
  box-shadow: var(--shadow);
}

.creneau-info {
  display: flex;
  gap: 12px;
  align-items: center;
  font-size: 14px;
}

.badge {
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 999px;
}

.badge-disponible {
  background: var(--color-success-bg);
  color: var(--color-success);
}

.badge-reserve {
  background: var(--color-warning-bg);
  color: var(--color-warning);
}
</style>