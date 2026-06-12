import { ref } from 'vue'

export interface ToastMessage {
  id: number
  message: string
  type: 'success' | 'error' | 'info' | 'warning'
}

const toasts = ref<ToastMessage[]>([])
let toastId = 0

export const toast = {
  toasts,
  show(message: string, type: 'success' | 'error' | 'info' | 'warning' = 'info') {
    const id = toastId++
    toasts.value.push({ id, message, type })
    setTimeout(() => {
      this.dismiss(id)
    }, 3500)
  },
  success(message: string) {
    this.show(message, 'success')
  },
  error(message: string) {
    this.show(message, 'error')
  },
  info(message: string) {
    this.show(message, 'info')
  },
  warning(message: string) {
    this.show(message, 'warning')
  },
  dismiss(id: number) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  },
}
