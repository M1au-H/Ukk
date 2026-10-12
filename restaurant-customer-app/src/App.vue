<script setup>
import { toastState } from './composables/useToast'
</script>

<template>
  <RouterView />
  <Transition name="toast">
    <div v-if="toastState.visible" class="toast" :class="toastState.type" role="status">
      {{ toastState.message }}
    </div>
  </Transition>
</template>
