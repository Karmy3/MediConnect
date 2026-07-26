<script setup lang="ts">
defineProps<{
  message: string
  type: 'success' | 'error'
}>()
</script>

<template>
  <div class="toast" :class="type">
    <span>{{ message }}</span>
  </div>
</template>

<style scoped>
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  box-shadow: var(--shadow-lg);
  z-index: 200;
  animation: slide-in 0.2s ease-out;
  max-width: 360px;
}

.toast.success {
  background: var(--color-success-bg);
  color: var(--color-success);
}

.toast.error {
  background: var(--color-danger-bg);
  color: var(--color-danger);
}

@keyframes slide-in {
  from {
    transform: translateY(10px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>