import { ref } from 'vue'

export type DialogType = 'success' | 'error' | 'warning' | 'info' | 'confirm'

export interface DialogOptions {
  type: DialogType
  title: string
  message: string
  confirmText?: string
  cancelText?: string
  onConfirm?: () => void
  onCancel?: () => void
}

const activeDialog = ref<DialogOptions | null>(null)

export const customDialog = {
  activeDialog,

  show(options: DialogOptions) {
    return new Promise<boolean>((resolve) => {
      activeDialog.value = {
        ...options,
        onConfirm: () => {
          if (options.onConfirm) options.onConfirm()
          resolve(true)
          activeDialog.value = null
        },
        onCancel: () => {
          if (options.onCancel) options.onCancel()
          resolve(false)
          activeDialog.value = null
        },
      }
    })
  },

  success(message: string, title = 'Sukses') {
    return this.show({
      type: 'success',
      title,
      message,
      confirmText: 'Oke',
    })
  },

  error(message: string, title = 'Gagal') {
    return this.show({
      type: 'error',
      title,
      message,
      confirmText: 'Oke',
    })
  },

  warning(message: string, title = 'Peringatan') {
    return this.show({
      type: 'warning',
      title,
      message,
      confirmText: 'Oke',
    })
  },

  info(message: string, title = 'Informasi') {
    return this.show({
      type: 'info',
      title,
      message,
      confirmText: 'Oke',
    })
  },

  confirm(message: string, title = 'Konfirmasi') {
    return this.show({
      type: 'confirm',
      title,
      message,
      confirmText: 'Ya',
      cancelText: 'Batal',
    })
  },
}
