import { reactive } from 'vue'

export const toastState = reactive({ message: '', type: 'info', visible: false })
let timer

export function toast(message, type = 'info', ms = 4000) {
  toastState.message = message
  toastState.type = type
  toastState.visible = true
  clearTimeout(timer)
  timer = setTimeout(() => (toastState.visible = false), ms)
}
