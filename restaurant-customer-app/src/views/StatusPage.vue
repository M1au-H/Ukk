<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue'
import { useSessionStore } from '../stores/session'
import { useOrdersStore } from '../stores/orders'
import { getEcho } from '../services/echo'
import { rupiah, jam } from '../services/format'
import { bayar } from '../services/snap'
import { toast } from '../composables/useToast'

const session = useSessionStore()
const store = useOrdersStore()

const STEPS = [
  { key: 'diterima_dapur', label: 'Diterima dapur' },
  { key: 'diproses', label: 'Diproses' },
  { key: 'siap_disajikan', label: 'Siap disajikan' },
  { key: 'selesai', label: 'Selesai' },
]

let echo = null
let timer = null
let channelName = ''

async function refresh() {
  try {
    await store.fetch(session.sessionToken)
  } catch {
    /* gagal sesaat tidak perlu mengganggu pengguna; akan dicoba lagi otomatis */
  }
}

onMounted(async () => {
  session.restore()
  await refresh()

  // Realtime: dapur mengubah status -> kartu pesanan ikut berubah tanpa refresh.
  echo = getEcho()
  channelName = `order.${session.sessionToken}`
  echo?.channel(channelName).listen('.OrderStatusUpdated', (e) => {
    store.applyKitchenStatus(e.order_id, e.kitchen_status)
  })

  // Konfirmasi pembayaran dari Midtrans datang beberapa detik setelah bayar, jadi selama masih
  // ada pesanan yang menunggu pembayaran, daftar dicek ulang tiap 5 detik.
  timer = setInterval(() => {
    if (store.hasPending) refresh()
  }, 5000)
})

onBeforeUnmount(() => {
  clearInterval(timer)
  if (channelName) echo?.leave(channelName)
})

// Nomor urut: pesanan paling awal = #1.
const list = computed(() => {
  const total = store.orders.length
  return store.orders.map((o, i) => ({ ...o, no: total - i }))
})

const stepIndex = (o) => STEPS.findIndex((s) => s.key === o.kitchen_status)

function itemText(it) {
  const opts = it.options.map((x) => x.value_snapshot).join(', ')
  return `${it.qty} x ${it.menu_name_snapshot}${opts ? ` (${opts})` : ''}`
}

async function lanjutBayar(o) {
  try {
    await bayar(o.snap_token, {
      onSuccess: refresh,
      onPending: refresh,
      onError: () => toast('Pembayaran gagal. Coba lagi.', 'error'),
      onClose: refresh,
    })
  } catch {
    toast('Halaman pembayaran belum bisa dibuka. Periksa koneksi, lalu coba lagi.', 'error')
  }
}
</script>

<template>
  <main class="page">
    <header class="topbar">
      <RouterLink :to="`/menu/${session.tableToken}`" class="btn btn-ghost">Kembali ke menu</RouterLink>
      <div class="topbar-title">
        <h1>Pesanan saya</h1>
        <p>Meja {{ session.tableNumber }}</p>
      </div>
    </header>

    <section v-if="!store.loaded" class="state">Memuat pesanan...</section>

    <section v-else-if="!list.length" class="state">
      <h2>Belum ada pesanan</h2>
      <p>Pesanan yang sudah dibayar akan muncul di sini, lengkap dengan statusnya.</p>
      <RouterLink :to="`/menu/${session.tableToken}`" class="btn btn-primary">Pilih menu</RouterLink>
    </section>

    <ul v-else class="orders">
      <li v-for="o in list" :key="o.id" class="order">
        <header class="order-head">
          <h2>Pesanan {{ o.no }}</h2>
          <p class="muted small">a.n. {{ o.customer_name }}, pukul {{ jam(o.created_at) }}</p>
        </header>

        <ul class="order-items">
          <li v-for="it in o.items" :key="it.id">
            {{ itemText(it) }}
            <span v-if="it.note" class="muted small block">Catatan: {{ it.note }}</span>
          </li>
        </ul>
        <p class="order-total">{{ rupiah(o.total_price) }}</p>

        <div v-if="o.payment_status === 'menunggu_pembayaran'" class="pay pay-wait">
          <p><strong>Menunggu pembayaran.</strong> Pesanan baru diteruskan ke dapur setelah pembayaran berhasil.</p>
          <button v-if="o.snap_token" type="button" class="btn btn-primary" @click="lanjutBayar(o)">Lanjutkan pembayaran</button>
        </div>
        <div v-else-if="o.payment_status === 'failed' || o.payment_status === 'expired'" class="pay pay-fail">
          <p><strong>{{ o.payment_status === 'expired' ? 'Waktu pembayaran habis.' : 'Pembayaran gagal.' }}</strong> Pesanan ini tidak diteruskan ke dapur. Silakan pesan lagi dari menu.</p>
        </div>
        <ol v-else class="steps" :aria-label="`Status pesanan ${o.no}`">
          <li
            v-for="(s, i) in STEPS"
            :key="s.key"
            :class="{ done: i < stepIndex(o), now: i === stepIndex(o) }"
            :aria-current="i === stepIndex(o) ? 'step' : undefined"
          >
            <span class="step-dot" aria-hidden="true"></span>
            <span class="step-label">{{ s.label }}</span>
          </li>
        </ol>
      </li>
    </ul>

    <div class="again">
      <RouterLink :to="`/menu/${session.tableToken}`" class="btn btn-primary">Pesan menu lagi</RouterLink>
    </div>
  </main>
</template>
