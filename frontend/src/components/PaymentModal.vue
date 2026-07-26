<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { loadStripe, type Stripe, type StripeElements, type StripeCardElement } from '@stripe/stripe-js'
import api from '../services/api'

const props = defineProps<{
  rendezVousId: number
}>()

const emit = defineEmits<{
  success: []
  close: []
}>()

const chargement = ref(false)
const erreur = ref('')
let stripe: Stripe | null = null
let elements: StripeElements | null = null
let cardElement: StripeCardElement | null = null

onMounted(async () => {
  stripe = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC_KEY)
  if (!stripe) {
    erreur.value = 'Impossible de charger Stripe.'
    return
  }

  elements = stripe.elements()
  cardElement = elements.create('card', {
    style: {
      base: {
        fontSize: '15px',
        color: '#134e4a',
        '::placeholder': { color: '#94a3b8' },
      },
    },
  })
  cardElement.mount('#card-element')
})

onUnmounted(() => {
  cardElement?.unmount()
})

async function payer() {
  if (!stripe || !cardElement) return
  erreur.value = ''
  chargement.value = true

  try {
    const { paymentMethod, error } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardElement,
    })

    if (error) {
      erreur.value = error.message || 'Erreur lors de la validation de la carte.'
      chargement.value = false
      return
    }

    await api.post(`/rendez-vous/${props.rendezVousId}/payer`, {
      payment_method: paymentMethod.id,
    })

    emit('success')
  } catch (e: any) {
    erreur.value = e.response?.data?.message || 'Erreur lors du paiement.'
  } finally {
    chargement.value = false
  }
}
</script>

<template>
  <div class="overlay" @click.self="emit('close')">
    <div class="modal">
      <h3>Paiement de la consultation</h3>
      <p class="hint">Carte de test : 4242 4242 4242 4242 — n'importe quelle date future et CVC.</p>

      <div id="card-element" class="card-element"></div>

      <p v-if="erreur" class="erreur">{{ erreur }}</p>

      <div class="actions">
        <button @click="emit('close')" class="btn-ghost">Annuler</button>
        <button @click="payer" :disabled="chargement" class="btn-primary">
          {{ chargement ? 'Paiement...' : 'Payer' }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: var(--color-white);
  border-radius: var(--radius);
  padding: 28px;
  width: 100%;
  max-width: 380px;
  box-shadow: var(--shadow-lg);
}

h3 {
  margin: 0 0 6px;
  font-size: 18px;
}

.hint {
  font-size: 12px;
  color: var(--color-text-muted);
  margin: 0 0 18px;
}

.card-element {
  padding: 12px 14px;
  border: 1px solid var(--color-border);
  border-radius: 10px;
  background: var(--color-white);
}

.erreur {
  color: var(--color-danger);
  font-size: 13px;
  background: var(--color-danger-bg);
  padding: 10px 12px;
  border-radius: 8px;
  margin: 14px 0 0;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.btn-ghost {
  background: var(--color-secondary);
  color: var(--color-text);
  border: none;
  padding: 10px 18px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
}

.btn-primary {
  background: var(--color-primary);
  color: var(--color-white);
  border: none;
  padding: 10px 18px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>