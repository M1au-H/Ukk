let loading = null

export function loadSnap() {
  if (window.snap) return Promise.resolve(window.snap)
  if (loading) return loading
  loading = new Promise((resolve, reject) => {
    const s = document.createElement('script')
    s.src = import.meta.env.VITE_MIDTRANS_SNAP_URL || 'https://app.sandbox.midtrans.com/snap/snap.js'
    s.setAttribute('data-client-key', import.meta.env.VITE_MIDTRANS_CLIENT_KEY || '')
    s.onload = () => resolve(window.snap)
    s.onerror = () => {
      loading = null
      reject(new Error('Halaman pembayaran tidak bisa dimuat.'))
    }
    document.head.appendChild(s)
  })
  return loading
}

// callbacks: { onSuccess, onPending, onError, onClose }
export async function bayar(snapToken, callbacks = {}) {
  const snap = await loadSnap()
  snap.pay(snapToken, callbacks)
}
