<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const rendezVous = ref<any[]>([])
const chargement = ref(true)
const erreur = ref('')

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

async function payer(rdvId: number) {
  try {
    // Pour la demo : utilise un payment_method de test Stripe (carte 4242...)
    // En production, ceci viendrait de Stripe Elements cote client
    const paymentMethod = prompt(
      'ID du moyen de paiement Stripe (pm_...) genere via curl avec tok_visa :'
    )
    if (!paymentMethod) return

    await api.post(`/rendez-vous/${rdvId}/payer`, { payment_method: paymentMethod })
    await chargerRendezVous()
    alert('Paiement effectue avec succes !')
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erreur lors du paiement.')
  }
}

async function annuler(rdvId: number) {
  if (!confirm('Confirmer l\'annulation de ce rendez-vous ?')) return
  try {
    await api.patch(`/rendez-vous/${rdvId}/annuler`)
    await chargerRendezVous()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erreur lors de l\'annulation.')
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

onMounted(chargerRendezVous)
</script>

<template>
  <div class="dashboard">
    <header class="topbar">
      <h1>MediConnect</h1>
      <div class="topbar-actions">
        <span>Bonjour, {{ authStore.user?.name }}</span>
        <button @click="deconnexion" class="btn-secondaire">Deconnexion</button>
      </div>
    </header>

    <main class="content">
      <div class="content-header">
        <h2>Mes rendez-vous</h2>
        <router-link to="/patient/reserver" class="btn-principal">
          + Prendre rendez-vous
        </router-link>
      </div>

      <p v-if="chargement">Chargement...</p>
      <p v-else-if="erreur" class="erreur">{{ erreur }}</p>
      <p v-else-if="!rendezVous.length" class="vide">
        Vous n'avez pas encore de rendez-vous.
      </p>

      <div v-else class="liste-rdv">
        <div v-for="rdv in rendezVous" :key="rdv.id" class="carte-rdv">
          <div class="carte-rdv-header">
            <strong>Dr {{ rdv.creneau?.medecin_profile?.user?.name || '...' }}</strong>
            <span class="badge" :class="'badge-' + rdv.statut">{{ statutLabel(rdv.statut) }}</span>
          </div>
          <p class="specialite">{{ rdv.creneau?.medecin_profile?.specialite }}</p>
          <p class="date">
            {{ new Date(rdv.creneau?.date_debut).toLocaleString('fr-FR') }}
          </p>
          <p v-if="rdv.analyse_ia" class="analyse-ia">
            <strong>Analyse IA :</strong> {{ rdv.analyse_ia }}
          </p>

          <div class="actions">
            <button v-if="rdv.statut === 'en_attente'" @click="payer(rdv.id)" class="btn-principal petit">
              Payer
            </button>
            <button
              v-if="['en_attente', 'paye', 'confirme'].includes(rdv.statut)"
              @click="annuler(rdv.id)"
              class="btn-danger petit"
            >
              Annuler
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dashboard { min-height: 100vh; background: #f4f6f8; }
.topbar { background: #fff; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.topbar h1 { color: #2563eb; margin: 0; font-size: 20px; }
.topbar-actions { display: flex; align-items: center; gap: 16px; font-size: 14px; }
.content { padding: 32px; max-width: 900px; margin: 0 auto; }
.content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.content-header h2 { margin: 0; }
.btn-principal { background: #2563eb; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; text-decoration: none; cursor: pointer; font-size: 14px; }
.btn-secondaire { background: #e5e7eb; color: #374151; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 13px; }
.btn-danger { background: #fee2e2; color: #dc2626; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 13px; }
.petit { padding: 6px 12px; font-size: 13px; }
.vide, .erreur { color: #6b7280; text-align: center; padding: 40px 0; }
.liste-rdv { display: flex; flex-direction: column; gap: 16px; }
.carte-rdv { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.carte-rdv-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.specialite { color: #6b7280; margin: 0 0 4px; font-size: 14px; }
.date { color: #111827; font-weight: 500; margin: 0 0 8px; }
.analyse-ia { background: #eff6ff; padding: 10px; border-radius: 6px; font-size: 13px; color: #1e40af; }
.actions { display: flex; gap: 8px; margin-top: 12px; }
.badge { font-size: 12px; padding: 4px 10px; border-radius: 999px; }
.badge-en_attente { background: #fef3c7; color: #92400e; }
.badge-paye { background: #dbeafe; color: #1e40af; }
.badge-confirme { background: #d1fae5; color: #065f46; }
.badge-termine { background: #e5e7eb; color: #374151; }
.badge-annule { background: #fee2e2; color: #991b1b; }
</style>