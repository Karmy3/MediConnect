<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import Logo from '../../components/Logo.vue'

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
  <div class="auth-page">
    <div class="auth-panel">
      <div class="brand">
        <Logo :size="28" />
        <span>MediConnect</span>
      </div>
      <h1>Rejoignez MediConnect</h1>
      <p class="tagline">Creez votre compte patient ou medecin et acceder a une sante connectee.</p>

      <ul class="perks">
        <li>Inscription rapide et securisee</li>
        <li>Espace patient ou espace medecin</li>
        <li>Donnees medicales protegees</li>
      </ul>
    </div>

    <div class="form-panel">
      <div class="form-card">
        <h2>Creer un compte</h2>
        <p class="subtitle">Rejoignez la plateforme en quelques instants</p>

        <form @submit.prevent="handleRegister">
          <label>
            Nom complet
            <input v-model="form.name" type="text" required placeholder="Jean Dupont" />
          </label>

          <label>
            Email
            <input v-model="form.email" type="email" required placeholder="vous@exemple.com" />
          </label>

          <label>
            Mot de passe
            <input v-model="form.password" type="password" required minlength="8" placeholder="8 caracteres min." />
          </label>

          <label>
            Confirmer le mot de passe
            <input v-model="form.password_confirmation" type="password" required />
          </label>

          <label>
            Je suis
            <div class="role-toggle">
              <button
                type="button"
                :class="{ active: form.role === 'patient' }"
                @click="form.role = 'patient'"
              >
                Patient
              </button>
              <button
                type="button"
                :class="{ active: form.role === 'medecin' }"
                @click="form.role = 'medecin'"
              >
                Medecin
              </button>
            </div>
          </label>

          <template v-if="form.role === 'medecin'">
            <label>
              Specialite
              <input v-model="form.specialite" type="text" placeholder="Generaliste, Cardiologue..." />
            </label>
            <label>
              Tarif de consultation (Ar)
              <input v-model="form.tarif_consultation" type="number" min="0" placeholder="25000" />
            </label>
          </template>

          <div v-if="Object.keys(erreurs).length" class="erreurs">
            <p v-for="(msgs, champ) in erreurs" :key="champ">{{ msgs[0] }}</p>
          </div>

          <button type="submit" :disabled="chargement" class="btn-primary">
            {{ chargement ? 'Creation...' : 'Creer mon compte' }}
          </button>
        </form>

        <p class="lien">
          Deja un compte ? <router-link to="/connexion">Se connecter</router-link>
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
  overflow-y: auto;
}

.form-card {
  width: 100%;
  max-width: 400px;
  padding: 24px 0;
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
  gap: 16px;
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

.role-toggle {
  display: flex;
  gap: 8px;
  background: var(--color-secondary);
  padding: 4px;
  border-radius: 10px;
}

.role-toggle button {
  flex: 1;
  padding: 10px;
  border: none;
  background: transparent;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-muted);
  cursor: pointer;
  transition: all 0.15s;
}

.role-toggle button.active {
  background: var(--color-white);
  color: var(--color-primary);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
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

.erreurs p {
  color: var(--color-danger);
  font-size: 13px;
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