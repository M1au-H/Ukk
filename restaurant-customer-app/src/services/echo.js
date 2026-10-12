import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echo = null

// Customer hanya memakai channel PUBLIK (menu-availability dan order.{session_token}),
// jadi tidak perlu token/auth apa pun untuk koneksi ini.
export function getEcho() {
  if (echo) return echo
  const key = import.meta.env.VITE_REVERB_APP_KEY
  if (!key) return null
  try {
    const port = Number(import.meta.env.VITE_REVERB_PORT || 8080)
    echo = new Echo({
      broadcaster: 'reverb',
      key,
      wsHost: import.meta.env.VITE_REVERB_HOST || location.hostname,
      wsPort: port,
      wssPort: port,
      forceTLS: false,
      enabledTransports: ['ws', 'wss'],
    })
  } catch {
    echo = null
  }
  return echo
}
