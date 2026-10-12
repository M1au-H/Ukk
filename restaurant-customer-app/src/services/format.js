const nf = new Intl.NumberFormat('id-ID')

export const rupiah = (n) => 'Rp' + nf.format(Math.round(Number(n) || 0))

export function jam(iso) {
  const d = new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
