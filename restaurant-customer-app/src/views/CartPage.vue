<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useSessionStore } from '../stores/session'
import { useCartStore } from '../stores/cart'
import api, { errorMessage } from '../services/api'
import { rupiah } from '../services/format'
import { bayar } from '../services/snap'
import { toast } from '../composables/useToast'

const router = useRouter()
const session = useSessionStore()
const cart = useCartStore()

const askName = ref(false)
const name = ref(localStorage.getItem('resto_last_name') || '')
const submitting = ref(false)

onMounted(() => {
  session.restore()
  cart.load(session.sessionToken)
})

function optionText(it) {
  return it.options.map((o) => o.label).join(', ')
}

function openName() {
  if (!cart.items.length) return
  askName.value = true
}

function goStatus() {
  router.replace({ name: 'status' })
}

async function submit() {
  const customerName = name.value.trim()
  if (!customerName) return toast('Isi nama dulu, supaya pelayan tahu pesanan ini milik siapa.', 'error')
  if (submitting.value) return
  submitting.value = true

  let order
  try {
    const { data } = await api.post('/orders', {
      table_token: session.tableToken,
      session_token: session.sessionToken,
      customer_name: customerName,
      items: cart.payload(),
    })
    order = data
  } catch (e) {
    // Order gagal dibuat (misal ada menu yang baru saja habis): keranjang dibiarkan utuh.
    toast(errorMessage(e, 'Pesanan belum bisa dibuat.'), 'error', 6000)
    submitting.value = false
    return
  }

  localStorage.setItem('resto_last_name', customerName)
  // Order sudah resmi dibuat di server -> keranjang dikosongkan (terkunci, tidak bisa diedit lagi).
  cart.clear()
  askName.value = false

  try {
    await bayar(order.snap_token, {
      onSuccess: goStatus,
      onPending: goStatus,
      onError: () => {
        toast('Pembayaran gagal. Kamu bisa mengulang dari halaman Pesanan saya.', 'error', 6000)
        goStatus()
      },
      onClose: () => {
        toast('Pembayaran belum selesai. Lanjutkan kapan saja dari halaman Pesanan saya.', 'info', 6000)
        goStatus()
      },
    })
  } catch {
    toast('Halaman pembayaran belum bisa dibuka. Lanjutkan dari halaman Pesanan saya.', 'error', 6000)
    goStatus()
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="page has-bar">
    <header class="topbar">
      <RouterLink :to="`/menu/${session.tableToken}`" class="btn btn-ghost">Kembali ke menu</RouterLink>
      <div class="topbar-title">
        <h1>Keranjang</h1>
        <p>Meja {{ session.tableNumber }}</p>
      </div>
    </header>

    <section v-if="!cart.items.length" class="state">
      <h2>Keranjang masih kosong</h2>
      <p>Pilih menu dulu, lalu kembali ke sini untuk memesan.</p>
      <RouterLink :to="`/menu/${session.tableToken}`" class="btn btn-primary">Lihat menu</RouterLink>
    </section>

    <template v-else>
      <ul class="cart-list">
        <li v-for="it in cart.items" :key="it.key" class="cart-row">
          <div class="cart-info">
            <p class="cart-name">{{ it.name }}</p>
            <p v-if="it.options.length" class="cart-opts">{{ optionText(it) }}</p>
            <p v-if="it.note" class="cart-opts">Catatan: {{ it.note }}</p>
            <p class="cart-price">{{ rupiah(cart.unitPrice(it) * it.qty) }}</p>
          </div>
          <div class="cart-side">
            <div class="stepper" :aria-label="`Jumlah ${it.name}`">
              <button type="button" aria-label="Kurangi" @click="cart.setQty(it.key, it.qty - 1)">-</button>
              <output>{{ it.qty }}</output>
              <button type="button" aria-label="Tambah" @click="cart.setQty(it.key, it.qty + 1)">+</button>
            </div>
            <button type="button" class="link-danger" @click="cart.remove(it.key)">Hapus</button>
          </div>
        </li>
      </ul>

      <p class="muted small cart-note">Keranjang masih bisa diubah sampai kamu menekan Pesan. Total akhir dihitung ulang oleh sistem saat pesanan dibuat.</p>

      <div class="checkout-bar">
        <div>
          <span class="muted small">Total</span>
          <p class="checkout-total">{{ rupiah(cart.total) }}</p>
        </div>
        <button type="button" class="btn btn-primary" @click="openName">Pesan</button>
      </div>
    </template>

    <div v-if="askName" class="sheet-backdrop center" @click.self="!submitting && (askName = false)">
      <form class="dialog" @submit.prevent="submit">
        <h2>Atas nama siapa pesanan ini?</h2>
        <p class="muted">Nama ini akan terlihat oleh dapur. Setelah itu kamu langsung diarahkan untuk membayar.</p>
        <label class="field">
          <span>Nama</span>
          <input v-model="name" type="text" maxlength="60" autocomplete="given-name" autofocus placeholder="Contoh: Budi" />
        </label>
        <div class="dialog-actions">
          <button type="button" class="btn btn-ghost" :disabled="submitting" @click="askName = false">Batal</button>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            {{ submitting ? 'Membuat pesanan...' : 'Lanjut bayar' }}
          </button>
        </div>
      </form>
    </div>
  </main>
</template>
