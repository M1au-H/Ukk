import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const PREFIX = 'resto_cart_'

function uid() {
  return Math.random().toString(36).slice(2, 10)
}

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  let storageKey = ''

  // Keranjang disimpan per sesi meja, jadi pindah meja = keranjang baru.
  function load(sessionToken) {
    storageKey = PREFIX + sessionToken
    for (let i = localStorage.length - 1; i >= 0; i--) {
      const k = localStorage.key(i)
      if (k && k.startsWith(PREFIX) && k !== storageKey) localStorage.removeItem(k)
    }
    try {
      items.value = JSON.parse(localStorage.getItem(storageKey)) || []
    } catch {
      items.value = []
    }
  }

  function save() {
    if (storageKey) localStorage.setItem(storageKey, JSON.stringify(items.value))
  }

  // Menu + opsi + catatan yang sama dianggap satu baris, jumlahnya ditambah.
  function signature(it) {
    const opts = it.options.map((o) => o.valueId).sort((a, b) => a - b).join(',')
    return [it.menuId, opts, (it.note || '').trim().toLowerCase()].join('|')
  }

  function add(item) {
    const existing = items.value.find((i) => signature(i) === signature(item))
    if (existing) existing.qty = Math.min(existing.qty + item.qty, 99)
    else items.value.push({ ...item, key: uid() })
    save()
  }

  function remove(key) {
    items.value = items.value.filter((i) => i.key !== key)
    save()
  }

  function setQty(key, qty) {
    const it = items.value.find((i) => i.key === key)
    if (!it) return
    if (qty < 1) return remove(key)
    it.qty = Math.min(qty, 99)
    save()
  }

  function clear() {
    items.value = []
    save()
  }

  const unitPrice = (it) => it.basePrice + it.options.reduce((s, o) => s + o.extraPrice, 0)
  const count = computed(() => items.value.reduce((s, i) => s + i.qty, 0))
  const total = computed(() => items.value.reduce((s, i) => s + unitPrice(i) * i.qty, 0))

  // Hanya ID dan jumlah yang dikirim. Harga TIDAK dikirim: server selalu menghitung ulang sendiri.
  function payload() {
    return items.value.map((i) => ({
      menu_id: i.menuId,
      qty: i.qty,
      option_value_ids: i.options.map((o) => o.valueId),
      note: i.note?.trim() || null,
    }))
  }

  return { items, count, total, load, add, remove, setQty, clear, unitPrice, payload }
})
