<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1 h-6 rounded-full bg-gradient-to-b from-retro-accent to-retro-primary"></div>
        <h2 class="text-sm font-semibold tracking-wider uppercase text-slate-800 dark:text-slate-200">
          Laporan Penjualan
        </h2>
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
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Tanggal</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Metode</th>
              <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-right text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">Total</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <tr
              v-for="t in list"
              :key="t.id"
              class="transition-colors duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800/50"
            >
              <td class="px-4 py-3 font-mono font-bold text-retro-primary">
                {{ t.kode_transaksi }}
              </td>
              <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                {{ formatDate(t.created_at) }}
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                  {{ t.metode_pembayaran }}
                </span>
              </td>
              <td class="px-4 py-3 font-bold text-right text-slate-800 dark:text-slate-100">
                {{ formatCurrency(t.total) }}
              </td>
            </tr>
            <tr v-if="list.length === 0">
              <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                Belum ada data
              </td>
            </tr>
          </tbody>
          <tfoot v-if="list.length > 0">
            <tr class="bg-slate-50/80 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-800">
              <td colspan="3" class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300">Total</td>
              <td class="px-4 py-3 text-xs font-bold text-right font-mono text-retro-primary">{{ formatCurrency(grandTotal) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type { Transaksi } from '@/types'
import { transaksiService } from '@/services'

const loading = ref(true)
const list = ref<Transaksi[]>([])
const grandTotal = computed(() => list.value.reduce((s, t) => s + Number(t.total), 0))

onMounted(async () => {
  try { const res = await transaksiService.getAll(); list.value = res.data as Transaksi[] } catch {}
  finally { loading.value = false }
})

function formatCurrency(v: number) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v) }
function formatDate(d: string) { return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }
</script>
