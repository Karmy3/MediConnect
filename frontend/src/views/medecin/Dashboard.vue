<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const rendezVous = ref<any[]>([])
const chargement = ref(true)

function initiales(nom: string) {
  return nom ? nom.split(' ').map((n) => n[0]).join('').slice(0, 2).toUpperCase() : '?'
}

async function chargerRendezVous() {
  chargement.value = true
  try {
    const response = await api.get('/rendez-vous-medecin')
    rendezVous.value = response.data
  } finally {
    chargement.value = false
  }
}

async function confirmer(rdvId: number) {
  try {
    await api.patch(`/rendez-vous/${rdvId}/confirmer`)
    await chargerRendezVous()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erreur lors de la confirmation.')
  }
}

async function analyserIA(rdvId: number) {
  try {
    const response = await api.post(`/rendez-vous/${rdvId}/analyser-ia`)
    alert('Analyse IA : ' + response.data.analyse_ia)
    await chargerRendezVous()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erreur lors de l\'analyse.')
  }
}

function deconnexion() {
  authStore.logout()
  router.push('/connexion')
}

function statutLabel(statut: string) {
  const labels: Record<string, string> = {
    en_attente: 'En attente de paiement',
    paye: 'Paye - a confirmer',
    confirme: 'Confirme',
    termine: 'Termine',
    annule: 'Annule',
  }
  return labels[statut] || statut
}

onMounted(chargerRendezVous)
</script>

<template>
  <div class="dashboard">
    <header class="topbar">
      <div class="brand">
        <div class="brand-icon">+</div>
        <span>MediConnect</span>
      </div>
      <div class="topbar-actions">
        <div class="user-chip">
          <div class="avatar">{{ initiales(authStore.user?.name || '') }}</div>
          <span>Dr {{ authStore.user?.name }}</span>
        </div>
        <router-link to="/medecin/creneaux" class="btn-ghost">Mes creneaux</router-link>
        <button @click="deconnexion" class="btn-ghost">Deconnexion</button>
      </div>
    </header>

    <main class="content">
      <div class="content-header">
        <div>
          <h1>Mes patients</h1>
          <p class="muted">Rendez-vous pris par vos patients</p>
        </div>
      </div>

      <div v-if="chargement" class="etat">Chargement...</div>
      <div v-else-if="!rendezVous.length" class="etat-vide">
        <div class="etat-vide-icon">🩺</div>
        <h3>Aucun rendez-vous pour le moment</h3>
        <p class="muted">Ajoutez des creneaux pour que les patients puissent reserver.</p>
        <router-link to="/medecin/creneaux" class="btn-primary">Gerer mes creneaux</router-link>
      </div>

      <div v-else class="liste-rdv">
        <div v-for="rdv in rendezVous" :key="rdv.id" class="carte-rdv">
          <div class="carte-rdv-top">
            <div class="patient-info">
              <div class="avatar avatar-lg">{{ initiales(rdv.patient?.name || '') }}</div>
              <div>
                <strong>{{ rdv.patient?.name }}</strong>
                <p class="specialite">{{ new Date(rdv.creneau?.date_debut).toLocaleString('fr-FR') }}</p>
              </div>
            </div>
            <span class="badge" :class="'badge-' + rdv.statut">{{ statutLabel(rdv.statut) }}</span>
          </div>

          <p v-if="rdv.symptomes_description" class="symptomes">
            <strong>Symptomes :</strong> {{ rdv.symptomes_description }}
          </p>
          <p v-if="rdv.analyse_ia" class="analyse-ia">
            <strong>🤖 Analyse IA :</strong> {{ rdv.analyse_ia }}
          </p>

          <div class="actions">
            <button
              v-if="rdv.statut === 'paye'"
              @click="confirmer(rdv.id)"
              class="btn-primary petit"
            >
              Confirmer le rendez-vous
            </button>
            <button
              v-if="rdv.symptomes_description && !rdv.analyse_ia"
              @click="analyserIA(rdv.id)"
              class="btn-secondary petit"
            >
              Analyser avec l'IA
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: var(--color-secondary);
}

.topbar {
  background: var(--color-white);
  padding: 18px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  position: sticky;
  top: 0;
  z-index: 10;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 18px;
  font-weight: 700;
  color: var(--color-primary-dark);
}

.brand-icon {
  width: 32px;
  height: 32px;
  background: var(--color-primary);
  color: var(--color-white);
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 700;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.user-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text);
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.avatar-lg {
  width: 46px;
  height: 46px;
  font-size: 16px;
}

.btn-ghost {
  background: var(--color-secondary);
  color: var(--color-text);
  border: none;
  padding: 9px 16px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.content {
  padding: 40px;
  max-width: 900px;
  margin: 0 auto;
}

.content-header {
  margin-bottom: 28px;
}

.content-header h1 {
  font-size: 26px;
}

.muted {
  color: var(--color-text-muted);
  font-size: 14px;
  margin: 6px 0 0;
}

.btn-primary {
  background: var(--color-primary);
  color: var(--color-white);
  border: none;
  padding: 12px 20px;
  border-radius: 10px;
  text-decoration: none;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  display: inline-block;
}

.btn-primary:hover {
  background: var(--color-primary-dark);
}

.btn-secondary {
  background: var(--color-secondary);
  color: var(--color-primary-dark);
  border: 1px solid var(--color-primary-light);
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

.etat {
  color: var(--color-text-muted);
  text-align: center;
  padding: 60px 0;
}

.etat-vide {
  text-align: center;
  padding: 60px 20px;
  background: var(--color-white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}

.etat-vide-icon {
  font-size: 40px;
  margin-bottom: 12px;
}

.etat-vide h3 {
  margin-bottom: 6px;
}

.etat-vide .btn-primary {
  margin-top: 20px;
}

.liste-rdv {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.carte-rdv {
  background: var(--color-white);
  border-radius: var(--radius);
  padding: 24px;
  box-shadow: var(--shadow);
}

.carte-rdv-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 14px;
}

.patient-info {
  display: flex;
  gap: 14px;
  align-items: center;
}

.patient-info strong {
  font-size: 15px;
}

.specialite {
  color: var(--color-text-muted);
  margin: 2px 0 0;
  font-size: 13px;
}

.symptomes {
  font-size: 14px;
  color: var(--color-text);
  background: var(--color-secondary);
  padding: 12px 14px;
  border-radius: 10px;
  margin-bottom: 10px;
}

.analyse-ia {
  background: var(--color-primary-light);
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 13px;
  color: var(--color-primary-dark);
  margin: 0 0 14px;
}

.actions {
  display: flex;
  gap: 10px;
}

.badge {
  font-size: 12px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 999px;
  white-space: nowrap;
}

.badge-en_attente {
  background: var(--color-warning-bg);
  color: var(--color-warning);
}

.badge-paye {
  background: #dbeafe;
  color: #1e40af;
}

.badge-confirme {
  background: var(--color-success-bg);
  color: var(--color-success);
}

.badge-termine {
  background: #e5e7eb;
  color: #374151;
}

.badge-annule {
  background: var(--color-danger-bg);
  color: var(--color-danger);
}
</style>