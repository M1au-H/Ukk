import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api, { errorMessage } from '../services/api'

export const useMenuStore = defineStore('menu', () => {
  const categories = ref([])
  const menus = ref([])
  const status = ref('idle') // idle | loading | ready | error
  const error = ref('')

  async function fetchAll() {
    status.value = 'loading'
    error.value = ''
    try {
      const [c, m] = await Promise.all([api.get('/categories'), api.get('/menus')])
      categories.value = c.data
      menus.value = m.data
      status.value = 'ready'
    } catch (e) {
      error.value = errorMessage(e, 'Menu belum bisa dimuat.')
      status.value = 'error'
    }
  }

  async function fetchDetail(id) {
    const { data } = await api.get(`/menus/${id}`)
    return data
  }

  // Dipanggil dari event realtime MenuAvailabilityChanged.
  function setAvailability(id, available) {
    const m = menus.value.find((x) => x.id === Number(id))
    if (m) m.is_available = !!available
  }

  const isAvailable = (id) => !!menus.value.find((x) => x.id === Number(id))?.is_available

  const grouped = computed(() =>
    categories.value
      .map((c) => ({ ...c, menus: menus.value.filter((m) => m.category_id === c.id) }))
      .filter((g) => g.menus.length),
  )

  return { categories, menus, status, error, grouped, fetchAll, fetchDetail, setAvailability, isAvailable }
})
