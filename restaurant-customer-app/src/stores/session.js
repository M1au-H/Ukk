import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

const KEY = 'resto_session'

// crypto.randomUUID() hanya ada di koneksi aman (https/localhost), sedangkan HP yang membuka
// lewat IP laptop (http) tidak punya itu. getRandomValues tersedia di keduanya.
function newSessionToken() {
  const bytes = new Uint8Array(16)
  crypto.getRandomValues(bytes)
  return 'sess_' + Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join('')
}

function read() {
  try {
    return JSON.parse(localStorage.getItem(KEY))
  } catch {
    return null
  }
}

export const useSessionStore = defineStore('session', () => {
  const tableToken = ref('')
  const tableNumber = ref('')
  const sessionToken = ref('')
  const status = ref('idle') // idle | loading | ready | invalid

  function restore() {
    if (sessionToken.value) return
    const s = read()
    if (s?.sessionToken) {
      tableToken.value = s.tableToken
      tableNumber.value = s.tableNumber
      sessionToken.value = s.sessionToken
    }
  }

  function persist() {
    localStorage.setItem(
      KEY,
      JSON.stringify({
        tableToken: tableToken.value,
        tableNumber: tableNumber.value,
        sessionToken: sessionToken.value,
      }),
    )
  }

  // Dipanggil saat halaman menu dibuka dari QR: cek token meja ke server, lalu siapkan sesi tamu.
  async function init(token) {
    status.value = 'loading'
    const stored = read()
    try {
      const { data } = await api.get(`/menu/resolve/${encodeURIComponent(token)}`)
      tableToken.value = token
      tableNumber.value = String(data.table_number)
      // Meja sama -> pakai sesi lama (riwayat order tetap ada). Meja beda -> sesi baru.
      sessionToken.value =
        stored?.tableToken === token && stored?.sessionToken ? stored.sessionToken : newSessionToken()
      persist()
      status.value = 'ready'
    } catch {
      status.value = 'invalid'
    }
  }

  return { tableToken, tableNumber, sessionToken, status, restore, init }
})
