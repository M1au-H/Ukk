import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useOrdersStore = defineStore('orders', () => {
  const orders = ref([])
  const loaded = ref(false)

  async function fetch(sessionToken) {
    const { data } = await api.get('/orders', { params: { session_token: sessionToken } })
    orders.value = data
    loaded.value = true
  }

  // Dipanggil dari event realtime OrderStatusUpdated.
  function applyKitchenStatus(orderId, status) {
    const o = orders.value.find((x) => x.id === orderId)
    if (o) o.kitchen_status = status
  }

  const hasPending = computed(() => orders.value.some((o) => o.payment_status === 'menunggu_pembayaran'))

  return { orders, loaded, hasPending, fetch, applyKitchenStatus }
})
