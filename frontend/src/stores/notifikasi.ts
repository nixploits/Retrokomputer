import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Notifikasi } from '@/types'
import { notifikasiService } from '@/services'

export const useNotifikasiStore = defineStore('notifikasi', () => {
  const items = ref<Notifikasi[]>([])
  const unreadCount = ref(0)
  const loading = ref(false)

  async function fetch() {
    loading.value = true
    try {
      const res = await notifikasiService.getAll()
      items.value = res.data
      unreadCount.value = items.value.filter((n) => !n.status_baca).length
    } catch {
      // silent fail
    } finally {
      loading.value = false
    }
  }

  async function markRead(id: number) {
    try {
      await notifikasiService.markRead(id)
      const item = items.value.find((n) => n.id === id)
      if (item) {
        item.status_baca = true
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch {
      // silent fail
    }
  }

  async function markAllRead() {
    try {
      await notifikasiService.markAllRead()
      items.value.forEach((n) => (n.status_baca = true))
      unreadCount.value = 0
    } catch {
      // silent fail
    }
  }

  return {
    items,
    unreadCount,
    loading,
    fetch,
    markRead,
    markAllRead,
  }
})
