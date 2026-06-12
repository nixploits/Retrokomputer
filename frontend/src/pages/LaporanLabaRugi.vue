<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1 h-6 rounded-full bg-gradient-to-b from-retro-accent to-retro-primary"></div>
        <h2 class="text-sm font-semibold tracking-wider uppercase text-slate-800 dark:text-slate-200">
          Laporan Laba Kotor
        </h2>
      </div>
    </div>

    <!-- Waterfall Chart -->
    <GrossProfitWaterfall />

    <!-- Detail Breakdown Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
      <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
          <span class="inline-block w-1.5 h-1.5 bg-retro-primary rounded-full"></span>
          Detail Ringkasan
        </h3>
      </div>
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">
        Memuat data laporan...
      </div>
      <div v-else class="p-6 space-y-4 text-xs font-mono">
        <div class="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-slate-800">
          <span class="text-slate-500 dark:text-slate-400">Total Penjualan</span>
          <span class="font-bold text-slate-700 dark:text-slate-200">{{ formatCurrency(stats.penjualan_bulan_ini) }}</span>
        </div>
        <div class="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-slate-800">
          <span class="text-slate-500 dark:text-slate-400">Total Pembelian (HPP)</span>
          <span class="font-bold text-slate-700 dark:text-slate-200">{{ formatCurrency(stats.pembelian_bulan_ini) }}</span>
        </div>
        <div class="flex justify-between items-center py-2.5 border-b border-slate-100 dark:border-slate-800">
          <span class="text-slate-500 dark:text-slate-400">Kerugian Inventaris</span>
          <span class="font-bold text-rose-600 dark:text-rose-400">{{ formatCurrency(stats.kerugian_inventaris) }}</span>
        </div>
        <div class="pt-4 flex justify-between items-center">
          <span class="font-bold text-sm uppercase tracking-wide text-slate-800 dark:text-slate-100">Laba Bersih</span>
          <span class="font-bold text-lg" :class="stats.laba_bersih >= 0 ? 'text-retro-success' : 'text-rose-600 dark:text-rose-400'">
            {{ formatCurrency(stats.laba_bersih) }}
          </span>
        </div>
        <div class="pt-3 text-[10px] text-center text-slate-400 dark:text-slate-500 border-t border-slate-100 dark:border-slate-800">
          Periode: Bulan ini
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { DashboardStats } from '@/types'
import { laporanService } from '@/services'
import GrossProfitWaterfall from '@/components/charts/GrossProfitWaterfall.vue'

const loading = ref(true)
const stats = ref<DashboardStats>({ penjualan_bulan_ini: 0, pembelian_bulan_ini: 0, laba_bersih: 0, total_transaksi: 0, kerugian_inventaris: 0 })

onMounted(async () => {
  try { const res = await laporanService.getDashboard(); stats.value = res.data } catch {}
  finally { loading.value = false }
})

function formatCurrency(v: number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v) }
</script>
