import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { CartItem, Produk } from '@/types'

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>([])
  const metode_pembayaran = ref<string>('tunai')

  const totalItems = computed(() => items.value.reduce((sum, item) => sum + item.qty, 0))

  const grandTotal = computed(() => items.value.reduce((sum, item) => sum + item.subtotal, 0))

  function addItem(produk: Produk, qty: number = 1) {
    const existing = items.value.find((i) => i.produk.id === produk.id)
    if (existing) {
      if (existing.qty + qty > produk.stok) {
        throw new Error(`Stok ${produk.nama_produk} tidak mencukupi. Tersedia: ${produk.stok}`)
      }
      existing.qty += qty
      existing.subtotal = existing.produk.harga_jual * existing.qty
    } else {
      if (qty > produk.stok) {
        throw new Error(`Stok ${produk.nama_produk} tidak mencukupi. Tersedia: ${produk.stok}`)
      }
      items.value.push({
        produk,
        qty,
        subtotal: produk.harga_jual * qty,
      })
    }
  }

  function updateQty(produkId: number, qty: number) {
    const item = items.value.find((i) => i.produk.id === produkId)
    if (item) {
      if (qty > item.produk.stok) {
        throw new Error(`Stok tidak mencukupi. Tersedia: ${item.produk.stok}`)
      }
      if (qty <= 0) {
        removeItem(produkId)
        return
      }
      item.qty = qty
      item.subtotal = item.produk.harga_jual * qty
    }
  }

  function removeItem(produkId: number) {
    items.value = items.value.filter((i) => i.produk.id !== produkId)
  }

  function clearCart() {
    items.value = []
    metode_pembayaran.value = 'tunai'
  }

  function getPayload() {
    return {
      items: items.value.map((i) => ({
        produk_id: i.produk.id,
        qty: i.qty,
      })),
      metode_pembayaran: metode_pembayaran.value,
    }
  }

  return {
    items,
    metode_pembayaran,
    totalItems,
    grandTotal,
    addItem,
    updateQty,
    removeItem,
    clearCart,
    getPayload,
  }
})
