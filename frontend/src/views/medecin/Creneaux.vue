<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../services/api'

const creneaux = ref<any[]>([])
const chargement = ref(true)
const erreur = ref('')

const dateDebut = ref('')
const dateFin = ref('')

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
  } catch (e: any) {
    erreur.value = e.response?.data?.message || 'Erreur lors de la creation du creneau.'
  }
}

async function supprimer(creneauId: number) {
  if (!confirm('Supprimer ce creneau ?')) return
  try {
    await api.delete(`/creneaux/${creneauId}`)
    await chargerCreneaux()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erreur lors de la suppression.')
  }
}

onMounted(chargerCreneaux)
</script>

<template>
  <div class="page">
    <div class="card">
      <router-link to="/medecin/tableau-de-bord" class="retour">← Retour</router-link>
      <h1>Mes creneaux de disponibilite</h1>

      <div class="formulaire">
        <label>
          Debut
          <input v-model="dateDebut" type="datetime-local" />
        </label>
        <label>
          Fin
          <input v-model="dateFin" type="datetime-local" />
        </label>
        <button @click="creerCreneau" class="btn-principal">Ajouter</button>
      </div>

      <p v-if="erreur" class="erreur">{{ erreur }}</p>

      <p v-if="chargement">Chargement...</p>
      <p v-else-if="!creneaux.length" class="vide">Aucun creneau cree pour le moment.</p>

      <div v-else class="liste">
        <div v-for="c in creneaux" :key="c.id" class="creneau">
          <div>
            <span>{{ new Date(c.date_debut).toLocaleString('fr-FR') }}</span>
            <span class="badge" :class="'badge-' + c.statut">{{ c.statut }}</span>
          </div>
          <button
            v-if="c.statut === 'disponible'"
            @click="supprimer(c.id)"
            class="btn-danger petit"
          >
            Supprimer
          </button>
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
.formulaire { display: flex; gap: 12px; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; }
label { display: flex; flex-direction: column; gap: 6px; font-size: 14px; color: #374151; }
input { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
.btn-principal { background: #2563eb; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-size: 14px; }
.btn-danger { background: #fee2e2; color: #dc2626; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 13px; }
.petit { padding: 6px 12px; font-size: 13px; }
.erreur { color: #dc2626; font-size: 14px; margin: 8px 0; }
.vide { color: #6b7280; text-align: center; padding: 40px 0; }
.liste { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; }
.creneau { display: flex; justify-content: space-between; align-items: center; background: #f9fafb; padding: 12px 16px; border-radius: 8px; }
.creneau > div { display: flex; gap: 10px; align-items: center; }
.badge { font-size: 12px; padding: 4px 10px; border-radius: 999px; }
.badge-disponible { background: #d1fae5; color: #065f46; }
.badge-reserve { background: #fef3c7; color: #92400e; }
</style>