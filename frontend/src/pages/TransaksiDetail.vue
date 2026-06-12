<template>
  <div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
      <router-link to="/transaksi" class="text-xs text-blue-600 hover:underline"
        >← Kembali</router-link
      >
      <button
        v-if="trx"
        @click="printThisReceipt"
        class="px-2.5 py-1 text-xs font-bold border-2 border-retro-orange text-retro-orange-dark rounded hover:bg-retro-orange hover:text-white transition-all font-mono flex items-center gap-1.5"
      >
        <svg
          class="w-3.5 h-3.5"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.615 0-1.115-.465-1.12-1.08L6 18m11.66 0H6.34m.665-4.171V6.375c0-.621.504-1.125 1.125-1.125h8.25c.621 0 1.125.504 1.125 1.125v7.454M16.5 7.5h.008v.008H16.5V7.5z"
          />
        </svg>
        CETAK NOTA
      </button>
    </div>
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400">Memuat...</div>
      <template v-else-if="trx">
        <div class="p-4 border-b border-slate-200 space-y-2">
          <div class="flex justify-between items-start">
            <div>
              <h2 class="text-sm font-semibold text-slate-800">{{ trx.kode_transaksi }}</h2>
              <p class="text-xs text-slate-500 mt-0.5">{{ formatDate(trx.created_at) }}</p>
            </div>
            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{
              trx.metode_pembayaran
            }}</span>
          </div>
          <div class="grid grid-cols-2 gap-4 text-xs pt-2 border-t border-slate-100">
            <div>
              <span class="text-slate-400 font-bold">KASIR:</span>
              <span class="ml-1 font-bold text-slate-750">{{
                trx.nama_kasir || trx.kasir?.name || '-'
              }}</span>
            </div>
            <div>
              <span class="text-slate-400 font-bold">PEMBELI:</span>
              <span class="ml-1 font-bold text-slate-750">{{ trx.nama_pembeli || '-' }}</span>
            </div>
          </div>
        </div>
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
              <th class="text-left text-xs font-medium text-slate-500 px-4 py-2">Produk</th>
              <th class="text-center text-xs font-medium text-slate-500 px-4 py-2">Qty</th>
              <th class="text-right text-xs font-medium text-slate-500 px-4 py-2">Harga</th>
              <th class="text-right text-xs font-medium text-slate-500 px-4 py-2">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in trx.details" :key="d.id" class="border-b border-slate-50">
              <td class="px-4 py-2 text-sm text-slate-800">{{ d.produk?.nama_produk || '-' }}</td>
              <td class="px-4 py-2 text-sm text-center text-slate-600">{{ d.qty }}</td>
              <td class="px-4 py-2 text-xs text-slate-600 text-right">
                {{ formatCurrency(d.harga_satuan) }}
              </td>
              <td class="px-4 py-2 text-xs font-medium text-slate-800 text-right">
                {{ formatCurrency(d.subtotal) }}
              </td>
            </tr>
          </tbody>
        </table>
        <div class="p-4 border-t border-slate-200 flex justify-between">
          <span class="text-sm font-semibold text-slate-800">Total</span>
          <span class="text-sm font-bold text-blue-600">{{ formatCurrency(trx.total) }}</span>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import type { Transaksi } from '@/types'
import { transaksiService, settingService } from '@/services'
import { printReceipt } from '@/utils/printReceipt'

const route = useRoute()
const loading = ref(true)
const trx = ref<Transaksi | null>(null)
const logoText = ref('Retro Komputer')

onMounted(async () => {
  try {
    const res = await transaksiService.getById(Number(route.params.id))
    trx.value = res.data
  } catch {
    /* silent */
  }

  try {
    const settingRes = await settingService.getActive()
    if (settingRes.data && settingRes.data.logo_text) {
      logoText.value = settingRes.data.logo_text
    }
  } catch {
    // silent
  } finally {
    loading.value = false
  }
})

function printThisReceipt() {
  if (trx.value) {
    printReceipt(trx.value, null, logoText.value)
  }
}

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}
function formatDate(d: string) {
  return new Date(d).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
