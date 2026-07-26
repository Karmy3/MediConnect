<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'patient',
  specialite: '',
  tarif_consultation: '',
})

const erreurs = ref<Record<string, string[]>>({})
const chargement = ref(false)

async function handleRegister() {
  erreurs.value = {}
  chargement.value = true

  try {
    await authStore.register(form.value)
    router.push(
      authStore.role === 'medecin' ? '/medecin/tableau-de-bord' : '/patient/tableau-de-bord'
    )
  } catch (e: any) {
    erreurs.value = e.response?.data?.errors || { general: [e.response?.data?.message || 'Erreur.'] }
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <div class="auth-container">
    <div class="auth-card">
      <h1>MediConnect</h1>
      <p class="subtitle">Créer un compte</p>

      <form @submit.prevent="handleRegister">
        <label>
          Nom complet
          <input v-model="form.name" type="text" required />
        </label>

        <label>
          Email
          <input v-model="form.email" type="email" required />
        </label>

        <label>
          Mot de passe
          <input v-model="form.password" type="password" required minlength="8" />
        </label>

        <label>
          Confirmer le mot de passe
          <input v-model="form.password_confirmation" type="password" required />
        </label>

        <label>
          Je suis
          <select v-model="form.role">
            <option value="patient">Patient</option>
            <option value="medecin">Médecin</option>
          </select>
        </label>

        <template v-if="form.role === 'medecin'">
          <label>
            Spécialité
            <input v-model="form.specialite" type="text" placeholder="Généraliste, Cardiologue..." />
          </label>
          <label>
            Tarif de consultation (Ar)
            <input v-model="form.tarif_consultation" type="number" min="0" />
          </label>
        </template>

        <div v-if="Object.keys(erreurs).length" class="erreurs">
          <p v-for="(msgs, champ) in erreurs" :key="champ">{{ msgs[0] }}</p>
        </div>

        <button type="submit" :disabled="chargement">
          {{ chargement ? 'Création...' : 'Créer mon compte' }}
        </button>
      </form>

      <p class="lien">
        Déjà un compte ? <router-link to="/connexion">Se connecter</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f4f6f8; padding: 20px; }
.auth-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 420px; }
h1 { margin: 0 0 4px; color: #2563eb; text-align: center; }
.subtitle { text-align: center; color: #6b7280; margin-bottom: 24px; }
form { display: flex; flex-direction: column; gap: 16px; }
label { display: flex; flex-direction: column; gap: 6px; font-size: 14px; color: #374151; }
input, select { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
input:focus, select:focus { outline: none; border-color: #2563eb; }
button { background: #2563eb; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 15px; cursor: pointer; margin-top: 8px; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.erreurs p { color: #dc2626; font-size: 14px; margin: 4px 0; }
.lien { text-align: center; margin-top: 20px; font-size: 14px; color: #6b7280; }
.lien a { color: #2563eb; text-decoration: none; }
</style>