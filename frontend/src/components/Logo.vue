<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: number
    color?: string
  }>(),
  {
    size: 22,
    color: '#0d9488',
  }
)
</script>

<template>
  <svg
    :width="size"
    :height="size"
    viewBox="0 0 100 100"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      d="M50 6 L50 94"
      :stroke="color"
      stroke-width="4"
      stroke-linecap="round"
    />
    <circle cx="50" cy="8" r="5" :fill="color" />
    <path
      d="M50 18 C 30 18, 20 10, 8 16 C 20 22, 26 28, 50 30 C 74 28, 80 22, 92 16 C 80 10, 70 18, 50 18 Z"
      :fill="color"
    />
    <path
      d="M50 30 C 34 34, 32 44, 50 48 C 68 52, 66 62, 50 66 C 34 70, 32 80, 50 84"
      :stroke="color"
      stroke-width="4"
      fill="none"
      stroke-linecap="round"
    />
    <path
      d="M50 30 C 66 34, 68 44, 50 48 C 32 52, 34 62, 50 66 C 66 70, 68 80, 50 84"
      :stroke="color"
      stroke-width="4"
      fill="none"
      stroke-linecap="round"
    />
  </svg>
</template>