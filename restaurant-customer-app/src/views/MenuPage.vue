<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useSessionStore } from '../stores/session'
import { useMenuStore } from '../stores/menu'
import { useCartStore } from '../stores/cart'
import { getEcho } from '../services/echo'
import { rupiah } from '../services/format'
import MenuDetailSheet from '../components/MenuDetailSheet.vue'

const route = useRoute()
const session = useSessionStore()
const menu = useMenuStore()
const cart = useCartStore()

const activeCategory = ref(0) // 0 = semua kategori
const openMenuId = ref(null)
let echo = null

async function start() {
  await session.init(route.params.tableToken)
  if (session.status !== 'ready') return
  cart.load(session.sessionToken)
  await menu.fetchAll()

  // Realtime: menu yang di-set habis oleh Admin langsung berubah di layar ini tanpa refresh.
  echo = getEcho()
  echo?.channel('menu-availability').listen('.MenuAvailabilityChanged', (e) => {
    menu.setAvailability(e.menu_id, e.is_available)
  })
}

onMounted(start)
onBeforeUnmount(() => echo?.leave('menu-availability'))

const groups = computed(() =>
  activeCategory.value === 0 ? menu.grouped : menu.grouped.filter((g) => g.id === activeCategory.value),
)

function openMenu(m) {
  if (m.is_available) openMenuId.value = m.id
}
</script>

<template>
  <main class="page has-bar">
    <header class="topbar">
      <div class="plate" aria-label="Nomor meja">
        <span class="plate-label">Meja</span>
        <span class="plate-no">{{ session.tableNumber || '-' }}</span>
      </div>
      <div class="topbar-title">
        <h1>Menu</h1>
        <p>Pilih menu, lalu bayar langsung dari HP.</p>
      </div>
      <RouterLink to="/status" class="btn btn-ghost">Pesanan saya</RouterLink>
    </header>

    <section v-if="session.status === 'loading' || session.status === 'idle'" class="state">Membuka meja...</section>

    <section v-else-if="session.status === 'invalid'" class="state state-error">
      <h2>Kode QR ini tidak bisa dipakai</h2>
      <p>Meja tidak ditemukan atau sedang tidak aktif. Minta bantuan pelayan, atau scan ulang kode QR di meja.</p>
    </section>

    <template v-else>
      <nav class="tabs" aria-label="Kategori menu">
        <button type="button" :class="{ on: activeCategory === 0 }" @click="activeCategory = 0">Semua</button>
        <button
          v-for="g in menu.grouped"
          :key="g.id"
          type="button"
          :class="{ on: activeCategory === g.id }"
          @click="activeCategory = g.id"
        >{{ g.name }}</button>
      </nav>

      <section v-if="menu.status === 'loading'" class="state">Memuat menu...</section>
      <section v-else-if="menu.status === 'error'" class="state state-error">
        <p>{{ menu.error }}</p>
        <button type="button" class="btn btn-primary" @click="menu.fetchAll()">Coba lagi</button>
      </section>
      <section v-else-if="!menu.grouped.length" class="state">Belum ada menu yang tersedia.</section>

      <section v-for="g in groups" :key="g.id" class="category">
        <h2>{{ g.name }}</h2>
        <ul class="menu-list">
          <li v-for="m in g.menus" :key="m.id" class="dish" :class="{ gone: !m.is_available }">
            <button type="button" class="dish-btn" :disabled="!m.is_available" @click="openMenu(m)">
              <img v-if="m.image_url" :src="m.image_url" alt="" class="dish-photo" loading="lazy" />
              <span class="dish-body">
                <span class="dish-line">
                  <span class="dish-name">{{ m.name }}</span>
                  <span class="leader" aria-hidden="true"></span>
                  <span class="dish-price">{{ rupiah(m.price) }}</span>
                </span>
                <span v-if="m.description" class="dish-desc">{{ m.description }}</span>
              </span>
              <span class="dish-action">{{ m.is_available ? 'Pilih' : 'Habis' }}</span>
            </button>
          </li>
        </ul>
      </section>
    </template>

    <RouterLink v-if="cart.count" to="/cart" class="cartbar">
      <span class="cartbar-count">{{ cart.count }}</span>
      <span class="cartbar-text">Lihat keranjang</span>
      <span class="cartbar-total">{{ rupiah(cart.total) }}</span>
    </RouterLink>

    <MenuDetailSheet v-if="openMenuId" :menu-id="openMenuId" @close="openMenuId = null" />
  </main>
</template>
