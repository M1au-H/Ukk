import axios from 'axios'

const base =
  import.meta.env.VITE_API_URL || `${location.protocol}//${location.hostname}:8000/api`

// Accept: application/json penting supaya Laravel selalu membalas JSON (termasuk saat error).
const api = axios.create({
  baseURL: base,
  headers: { Accept: 'application/json' },
  timeout: 20000,
})

export function errorMessage(err, fallback = 'Terjadi kesalahan. Coba lagi.') {
  const res = err?.response
  if (!res) return 'Tidak bisa terhubung ke server. Periksa koneksi internet.'
  if (res.status === 429) return 'Terlalu banyak percobaan. Tunggu sebentar, lalu coba lagi.'
  const errors = res.data?.errors
  if (errors) {
    const first = Object.values(errors).flat()[0]
    if (first) return first
  }
  return res.data?.message || fallback
}

export default api
