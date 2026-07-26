<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const erreur = ref('')
const chargement = ref(false)

async function handleLogin() {
  erreur.value = ''
  chargement.value = true

  try {
    await authStore.login(email.value, password.value)
    router.push(
      authStore.role === 'medecin' ? '/medecin/tableau-de-bord' : '/patient/tableau-de-bord'
    )
  } catch (e: any) {
    erreur.value = e.response?.data?.message || 'Erreur de connexion.'
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <div class="auth-container">
    <div class="auth-card">
      <h1>MediConnect</h1>
      <p class="subtitle">Connexion à votre espace</p>

      <form @submit.prevent="handleLogin">
        <label>
          Email
          <input v-model="email" type="email" required placeholder="vous@exemple.com" />
        </label>

        <label>
          Mot de passe
          <input v-model="password" type="password" required placeholder="••••••••" />
        </label>

        <p v-if="erreur" class="erreur">{{ erreur }}</p>

        <button type="submit" :disabled="chargement">
          {{ chargement ? 'Connexion...' : 'Se connecter' }}
        </button>
      </form>

      <p class="lien">
        Pas encore de compte ? <router-link to="/inscription">S'inscrire</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-container { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f4f6f8; padding: 20px; }
.auth-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); width: 100%; max-width: 380px; }
h1 { margin: 0 0 4px; color: #2563eb; text-align: center; }
.subtitle { text-align: center; color: #6b7280; margin-bottom: 24px; }
form { display: flex; flex-direction: column; gap: 16px; }
label { display: flex; flex-direction: column; gap: 6px; font-size: 14px; color: #374151; }
input { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
input:focus { outline: none; border-color: #2563eb; }
button { background: #2563eb; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 15px; cursor: pointer; margin-top: 8px; }
button:disabled { opacity: 0.6; cursor: not-allowed; }
.erreur { color: #dc2626; font-size: 14px; margin: 0; }
.lien { text-align: center; margin-top: 20px; font-size: 14px; color: #6b7280; }
.lien a { color: #2563eb; text-decoration: none; }
</style>