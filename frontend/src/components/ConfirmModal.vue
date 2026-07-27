<script setup lang="ts">
defineProps<{
  titre: string
  message: string
}>()

const emit = defineEmits<{
  confirm: []
  cancel: []
}>()
</script>

<template>
  <div class="overlay" @click.self="emit('cancel')">
    <div class="modal">
      <h3>{{ titre }}</h3>
      <p>{{ message }}</p>
      <div class="actions">
        <button @click="emit('cancel')" class="btn-ghost">Annuler</button>
        <button @click="emit('confirm')" class="btn-danger">Confirmer</button>
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
  max-width: 360px;
  box-shadow: var(--shadow-lg);
}

h3 {
  margin: 0 0 10px;
  font-size: 17px;
}

p {
  margin: 0;
  font-size: 14px;
  color: var(--color-text-muted);
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 22px;
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

.btn-danger {
  background: var(--color-danger);
  color: var(--color-white);
  border: none;
  padding: 10px 18px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
}
</style>