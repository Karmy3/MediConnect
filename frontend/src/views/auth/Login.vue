<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import Logo from '../../components/Logo.vue'

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
  <div class="auth-page">
    <div class="auth-panel">
      <div class="brand">
        <Logo :size="28" />
        <span>MediConnect</span>
      </div>
      <h1>Bon retour parmi nous</h1>
      <p class="tagline">Connectez-vous pour gerer vos consultations et rendez-vous medicaux.</p>

      <ul class="perks">
        <li>Consultations avec des medecins verifies</li>
        <li>Rendez-vous en quelques clics</li>
        <li>Suivi medical securise</li>
      </ul>
    </div>

    <div class="form-panel">
      <div class="form-card">
        <h2>Connexion</h2>
        <p class="subtitle">Accedez a votre espace MediConnect</p>

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

          <button type="submit" :disabled="chargement" class="btn-primary">
            {{ chargement ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>

        <p class="lien">
          Pas encore de compte ? <router-link to="/inscription">Creer un compte</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  display: flex;
  min-height: 100vh;
}

.auth-panel {
  flex: 1;
  background: linear-gradient(160deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
  color: var(--color-white);
  padding: 56px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 20px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 20px;
  font-weight: 700;
  margin-bottom: 24px;
}

.auth-panel h1 {
  font-size: 34px;
  line-height: 1.25;
  max-width: 420px;
}

.tagline {
  color: rgba(255, 255, 255, 0.85);
  max-width: 380px;
  line-height: 1.6;
}

.perks {
  list-style: none;
  padding: 0;
  margin: 20px 0 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.perks li {
  padding-left: 26px;
  position: relative;
  color: rgba(255, 255, 255, 0.92);
}

.perks li::before {
  content: '✓';
  position: absolute;
  left: 0;
  font-weight: 700;
}

.form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-white);
  padding: 40px;
}

.form-card {
  width: 100%;
  max-width: 380px;
}

.form-card h2 {
  font-size: 26px;
}

.subtitle {
  color: var(--color-text-muted);
  margin: 6px 0 28px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text);
}

input {
  padding: 12px 14px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  font-size: 14px;
  font-weight: 400;
  transition: border-color 0.15s;
}

input:focus {
  outline: none;
  border-color: var(--color-primary);
}

.btn-primary {
  background: var(--color-primary);
  color: var(--color-white);
  border: none;
  padding: 13px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 6px;
  transition: background 0.15s;
}

.btn-primary:hover:not(:disabled) {
  background: var(--color-primary-dark);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.erreur {
  color: var(--color-danger);
  font-size: 14px;
  margin: 0;
  background: var(--color-danger-bg);
  padding: 10px 12px;
  border-radius: 8px;
}

.lien {
  text-align: center;
  margin-top: 24px;
  font-size: 14px;
  color: var(--color-text-muted);
}

.lien a {
  font-weight: 600;
  text-decoration: none;
}

@media (max-width: 900px) {
  .auth-panel {
    display: none;
  }
}
</style>