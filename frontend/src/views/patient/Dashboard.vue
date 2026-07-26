<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import Logo from '../../components/Logo.vue'
import PaymentModal from '../../components/PaymentModal.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'
import Toast from '../../components/Toast.vue'

const router = useRouter()
const authStore = useAuthStore()

const rendezVous = ref<any[]>([])
const chargement = ref(true)
const erreur = ref('')
const rdvAPayer = ref<number | null>(null)
const rdvAAnnuler = ref<number | null>(null)
const toast = ref<{ message: string; type: 'success' | 'error' } | null>(null)

async function chargerRendezVous() {
  chargement.value = true
  try {
    const response = await api.get('/mes-rendez-vous')
    rendezVous.value = response.data
  } catch (e: any) {
    erreur.value = 'Impossible de charger vos rendez-vous.'
  } finally {
    chargement.value = false
  }
}

function afficherToast(message: string, type: 'success' | 'error') {
  toast.value = { message, type }
  setTimeout(() => (toast.value = null), 3500)
}

function ouvrirPaiement(rdvId: number) {
  rdvAPayer.value = rdvId
}

async function surPaiementReussi() {
  rdvAPayer.value = null
  await chargerRendezVous()
  afficherToast('Paiement effectue avec succes.', 'success')
}

function demanderAnnulation(rdvId: number) {
  rdvAAnnuler.value = rdvId
}

async function confirmerAnnulation() {
  if (!rdvAAnnuler.value) return
  try {
    await api.patch(`/rendez-vous/${rdvAAnnuler.value}/annuler`)
    await chargerRendezVous()
    afficherToast('Rendez-vous annule.', 'success')
  } catch (e: any) {
    afficherToast(e.response?.data?.message || 'Erreur lors de l\'annulation.', 'error')
  } finally {
    rdvAAnnuler.value = null
  }
}

function deconnexion() {
  authStore.logout()
  router.push('/connexion')
}

function statutLabel(statut: string) {
  const labels: Record<string, string> = {
    en_attente: 'En attente de paiement',
    paye: 'Paye - en attente de confirmation',
    confirme: 'Confirme',
    termine: 'Termine',
    annule: 'Annule',
  }
  return labels[statut] || statut
}

function initiales(nom: string) {
  return nom ? nom.split(' ').map((n) => n[0]).join('').slice(0, 2).toUpperCase() : '?'
}

onMounted(chargerRendezVous)
</script>

<template>
  <div class="dashboard">
    <header class="topbar">
      <div class="brand">
        <Logo :size="26" color="#0d9488" />
        <span>MediConnect</span>
      </div>
      <div class="topbar-actions">
        <div class="user-chip">
          <div class="avatar">{{ initiales(authStore.user?.name || '') }}</div>
          <span>{{ authStore.user?.name }}</span>
        </div>
        <button @click="deconnexion" class="btn-ghost">Deconnexion</button>
      </div>
    </header>

    <main class="content">
      <div class="content-header">
        <div>
          <h1>Mes rendez-vous</h1>
          <p class="muted">Gerez vos consultations medicales en un coup d'oeil</p>
        </div>
        <router-link to="/patient/reserver" class="btn-primary">
          + Prendre rendez-vous
        </router-link>
      </div>

      <div v-if="chargement" class="etat">Chargement de vos rendez-vous...</div>
      <div v-else-if="erreur" class="etat erreur">{{ erreur }}</div>
      <div v-else-if="!rendezVous.length" class="etat-vide">
        <div class="etat-vide-icon">📅</div>
        <h3>Aucun rendez-vous pour le moment</h3>
        <p class="muted">Prenez rendez-vous avec un medecin pour commencer.</p>
        <router-link to="/patient/reserver" class="btn-primary">Prendre rendez-vous</router-link>
      </div>

      <div v-else class="liste-rdv">
        <div v-for="rdv in rendezVous" :key="rdv.id" class="carte-rdv">
          <div class="carte-rdv-top">
            <div class="medecin-info">
              <div class="avatar avatar-lg">{{ initiales(rdv.creneau?.medecin_profile?.user?.name || '') }}</div>
              <div>
                <strong>Dr {{ rdv.creneau?.medecin_profile?.user?.name || '...' }}</strong>
                <p class="specialite">{{ rdv.creneau?.medecin_profile?.specialite }}</p>
              </div>
            </div>
            <span class="badge" :class="'badge-' + rdv.statut">{{ statutLabel(rdv.statut) }}</span>
          </div>

          <div class="carte-rdv-details">
            <div class="detail-item">
              <span class="detail-label">Date</span>
              <span>{{ new Date(rdv.creneau?.date_debut).toLocaleString('fr-FR') }}</span>
            </div>
          </div>

          <p v-if="rdv.analyse_ia" class="analyse-ia">
            <strong>🤖 Analyse IA :</strong> {{ rdv.analyse_ia }}
          </p>

          <div class="actions">
            <button v-if="rdv.statut === 'en_attente'" @click="ouvrirPaiement(rdv.id)" class="btn-primary petit">
              Payer la consultation
            </button>
            <button
              v-if="['en_attente', 'paye', 'confirme'].includes(rdv.statut)"
              @click="demanderAnnulation(rdv.id)"
              class="btn-danger petit"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </main>

    <PaymentModal
      v-if="rdvAPayer"
      :rendez-vous-id="rdvAPayer"
      @success="surPaiementReussi"
      @close="rdvAPayer = null"
    />

    <ConfirmModal
      v-if="rdvAAnnuler"
      titre="Annuler ce rendez-vous ?"
      message="Cette action liberera le creneau et ne pourra pas etre annulee."
      @confirm="confirmerAnnulation"
      @cancel="rdvAAnnuler = null"
    />

    <Toast v-if="toast" :message="toast.message" :type="toast.type" />
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

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 16px;
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
}

.content {
  padding: 40px;
  max-width: 900px;
  margin: 0 auto;
}

.content-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 28px;
  gap: 16px;
  flex-wrap: wrap;
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
  transition: background 0.15s;
  display: inline-block;
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

.etat {
  color: var(--color-text-muted);
  text-align: center;
  padding: 60px 0;
}

.etat.erreur {
  color: var(--color-danger);
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
  margin-bottom: 16px;
}

.medecin-info {
  display: flex;
  gap: 14px;
  align-items: center;
}

.medecin-info strong {
  font-size: 15px;
}

.specialite {
  color: var(--color-text-muted);
  margin: 2px 0 0;
  font-size: 13px;
}

.carte-rdv-details {
  display: flex;
  gap: 24px;
  padding: 12px 0;
  border-top: 1px solid var(--color-border);
  border-bottom: 1px solid var(--color-border);
  margin-bottom: 14px;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 14px;
}

.detail-label {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--color-text-muted);
  font-weight: 600;
}

.analyse-ia {
  background: var(--color-secondary);
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