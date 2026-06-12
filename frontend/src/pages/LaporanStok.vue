<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1 h-6 rounded-full bg-gradient-to-b from-retro-accent to-retro-primary"></div>
        <h2 class="text-sm font-semibold tracking-wider uppercase text-slate-800 dark:text-slate-200">
          Laporan Stok
        </h2>
      </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1: Jenis Produk -->
      <div class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Jenis Produk</p>
        <p class="text-xl font-bold tracking-tight mt-1.5 text-slate-800 dark:text-slate-100">{{ stokStats.total_jenis_produk }}</p>
      </div>
      <!-- Card 2: Stok Aman -->
      <div class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-retro-primary/20 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Stok Aman</p>
        <p class="text-xl font-bold tracking-tight mt-1.5 text-retro-primary">{{ stokStats.stok_aman }}</p>
      </div>
      <!-- Card 3: Stok Rendah -->
      <div class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-amber-500/20 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Stok Rendah</p>
        <p class="text-xl font-bold tracking-tight mt-1.5 text-amber-500 dark:text-amber-400">{{ stokStats.stok_rendah }}</p>
      </div>
      <!-- Card 4: Stok Habis -->
      <div class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-red-500/20 rounded-xl p-5 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Stok Habis</p>
        <p class="text-xl font-bold tracking-tight mt-1.5 text-rose-500 dark:text-rose-400">{{ stokStats.stok_habis }}</p>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">
        Memuat data laporan...
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-800">
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Kode</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Produk</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Kategori</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-center text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Stok</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-center text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Minimum</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Status</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <tr
              v-for="p in produkList"
              :key="p.id"
              class="transition-colors duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800/50"
            >
              <td class="px-4 py-3 font-mono font-bold text-retro-primary">
                {{ p.kode_produk }}
              </td>
              <td class="px-4 py-3 font-semibold font-sans text-slate-700 dark:text-slate-300">
                {{ p.nama_produk }}
              </td>
              <td class="px-4 py-3 font-sans text-slate-500 dark:text-slate-400">
                {{ p.kategori }}
              </td>
              <td class="px-4 py-3 text-center font-bold font-mono" :style="{ color: p.stok <= 0 ? '#f87171' : p.stok <= p.stok_minimum ? '#fbbf24' : '#34d399' }">
                {{ p.stok }}
              </td>
              <td class="px-4 py-3 text-center font-mono text-slate-500 dark:text-slate-400">
                {{ p.stok_minimum }}
              </td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase border"
                  :style="{
                    background: p.stok <= 0 ? 'rgba(239,68,68,0.08)' : p.stok <= p.stok_minimum ? 'rgba(245,158,11,0.08)' : 'rgba(16,185,129,0.08)',
                    color: p.stok <= 0 ? '#fb7185' : p.stok <= p.stok_minimum ? '#fbbf24' : '#34d399',
                    borderColor: p.stok <= 0 ? 'rgba(239,68,68,0.15)' : p.stok <= p.stok_minimum ? 'rgba(245,158,11,0.15)' : 'rgba(16,185,129,0.15)'
                  }"
                >
                  {{ p.stok <= 0 ? 'Habis' : p.stok <= p.stok_minimum ? 'Rendah' : 'Aman' }}
                </span>
              </td>
            </tr>
            <tr v-if="produkList.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                Belum ada data
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Produk, StokStats } from '@/types'
import { produkService, laporanService } from '@/services'

const loading = ref(true)
const produkList = ref<Produk[]>([])
const stokStats = ref<StokStats>({ total_jenis_produk: 0, stok_aman: 0, stok_rendah: 0, stok_habis: 0 })

onMounted(async () => {
  try {
    const [pRes, sRes] = await Promise.all([produkService.getAll(), laporanService.getStok()])
    produkList.value = pRes.data as Produk[]
    stokStats.value = sRes.data
  } catch {}
  finally { loading.value = false }
})
</script>
