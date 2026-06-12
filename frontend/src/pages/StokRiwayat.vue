<template>
  <div class="space-y-4 font-mono">
    <!-- Header with Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-2">
        <span class="text-xl font-bold text-retro-blue"></span>
        <h2 class="text-base font-bold text-slate-800 uppercase tracking-wider">
          RIWAYAT MUTASI STOK
        </h2>
      </div>

      <!-- Date Filter Dropdown -->
      <div class="flex items-center gap-2">
        <span class="text-xs font-bold text-slate-500 uppercase">Filter Waktu:</span>
        <select
          v-model="selectedFilter"
          class="px-2.5 py-1.5 text-xs font-bold border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue bg-white"
        >
          <option value="semua">Semua Waktu</option>
          <option value="minggu">Minggu Ini</option>
          <option value="bulan">Bulan Ini</option>
          <option value="tahun">Tahun Ini</option>
        </select>
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-lg border-2 border-slate-200 overflow-hidden shadow-sm">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 font-mono">
        Memuat riwayat mutasi stok...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-200 bg-slate-50 font-bold text-xs text-slate-700">
              <th class="px-4 py-3">PRODUK</th>
              <th class="px-4 py-3">TIPE</th>
              <th class="px-4 py-3 text-center">QTY MUTASI</th>
              <th class="px-4 py-3">SUMBER</th>
              <th class="px-4 py-3">TANGGAL</th>
            </tr>
          </thead>
          <tbody class="text-xs text-slate-600">
            <tr
              v-for="r in filteredList"
              :key="r.id"
              class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors"
            >
              <td class="px-4 py-3">
                <span class="block font-bold text-slate-800">{{
                  r.produk?.nama_produk || '-'
                }}</span>
                <span class="text-[10px] text-slate-400 font-mono"
                  >Kode: {{ r.produk?.kode_produk || '-' }}</span
                >
              </td>
              <td class="px-4 py-3">
                <!-- Redesigned tipe indicator: bright neon colors with glow -->
                <span
                  v-if="r.tipe === 'masuk'"
                  class="font-black text-emerald-400 uppercase tracking-wide text-xs"
                  style="
                    text-shadow:
                      0 0 8px rgba(52, 211, 153, 0.6),
                      0 0 16px rgba(52, 211, 153, 0.3);
                  "
                >
                  ▲ MASUK
                </span>
                <span
                  v-else
                  class="font-black text-rose-400 uppercase tracking-wide text-xs"
                  style="
                    text-shadow:
                      0 0 8px rgba(251, 113, 133, 0.6),
                      0 0 16px rgba(251, 113, 133, 0.3);
                  "
                >
                  ▼ KELUAR
                </span>
              </td>
              <td class="px-4 py-3 text-center font-bold font-mono text-slate-800">
                {{ r.qty }} pcs
              </td>
              <td class="px-4 py-3 font-semibold text-slate-750">
                {{ r.sumber }}
              </td>
              <td class="px-4 py-3 text-slate-500">
                {{ formatDate(r.created_at) }}
              </td>
            </tr>
            <tr v-if="filteredList.length === 0">
              <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400 font-mono">
                Tidak ada data riwayat mutasi stok untuk filter ini.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type { RiwayatStok } from '@/types'
import { laporanService } from '@/services'

const loading = ref(true)
const list = ref<RiwayatStok[]>([])
const selectedFilter = ref('semua')

onMounted(async () => {
  try {
    const res = await laporanService.getRiwayatStok()
    list.value = res.data as RiwayatStok[]
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
})

// Date filter logic purely in frontend for maximum speed and compatibility
const filteredList = computed(() => {
  if (selectedFilter.value === 'semua') return list.value

  const now = new Date()

  // start of 7 days ago
  const startOfWeek = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7)
  const currentMonth = now.getMonth()
  const currentYear = now.getFullYear()

  return list.value.filter((item) => {
    const itemDate = new Date(item.created_at)
    if (selectedFilter.value === 'minggu') {
      return itemDate >= startOfWeek
    } else if (selectedFilter.value === 'bulan') {
      return itemDate.getMonth() === currentMonth && itemDate.getFullYear() === currentYear
    } else if (selectedFilter.value === 'tahun') {
      return itemDate.getFullYear() === currentYear
    }
    return true
  })
})

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
