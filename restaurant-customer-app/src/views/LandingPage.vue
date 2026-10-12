<script setup>
import { computed } from 'vue'
import { useSessionStore } from '../stores/session'

const session = useSessionStore()
session.restore()
const lastMenu = computed(() => (session.tableToken ? `/menu/${session.tableToken}` : null))
</script>

<template>
  <main class="landing">
    <h1>Scan kode QR di meja Anda</h1>
    <p>Menu dan pemesanan dibuka lewat kode QR yang ada di meja. Arahkan kamera HP ke kode itu, lalu ketuk tautan yang muncul.</p>
    <RouterLink v-if="lastMenu" :to="lastMenu" class="btn btn-primary">Kembali ke menu Meja {{ session.tableNumber }}</RouterLink>
  </main>
</template>
