<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1 h-6 rounded-full bg-gradient-to-b from-retro-accent to-retro-primary"></div>
        <h2
          class="text-sm font-semibold tracking-wider uppercase text-slate-800 dark:text-slate-200"
        >
          {{ isOwner ? 'Analisis Penjualan' : 'Riwayat Transaksi' }}
        </h2>
      </div>
      <div
        v-if="isOwner"
        class="text-[11px] font-medium px-2.5 py-1 rounded-full bg-retro-primary/10 dark:bg-retro-primary/20 text-retro-primary dark:text-retro-primary border border-retro-primary/20"
      >
        Owner
      </div>
    </div>

    <!-- OWNER VIEW: Premium Dashboard -->
    <div v-if="isOwner && !loading" class="space-y-5">
      <!-- KPI Cards Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Card 1: Total Omzet -->
        <div
          class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-retro-accent/20 rounded-xl p-5 transition-all duration-300 hover:shadow-lg dark:hover:shadow-retro-accent/5 hover:-translate-y-0.5"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center bg-retro-accent/10 dark:bg-retro-accent/20 text-retro-accent"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <span
                class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400"
                >Total Omzet</span
              >
            </div>
          </div>
          <div class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
            Rp {{ formatRupiah(totalRevenue) }}
          </div>
          <div class="text-[10px] mt-1.5 text-slate-400 dark:text-slate-500">
            Volume penjualan terkumpul
          </div>
          <!-- Subtle accent line -->
          <div
            class="mt-3 h-[2px] rounded-full bg-gradient-to-r from-retro-accent to-transparent dark:from-retro-accent opacity-40"
          ></div>
        </div>

        <!-- Card 2: Total Transaksi -->
        <div
          class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-retro-primary/20 rounded-xl p-5 transition-all duration-300 hover:shadow-lg dark:hover:shadow-retro-primary/5 hover:-translate-y-0.5"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center bg-retro-primary/10 dark:bg-retro-primary/20 text-retro-primary"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                  />
                </svg>
              </div>
              <span
                class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400"
                >Transaksi</span
              >
            </div>
          </div>
          <div class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
            {{ transaksiList.length }} <span class="text-sm font-normal text-slate-400">trx</span>
          </div>
          <div class="text-[10px] mt-1.5 text-slate-400 dark:text-slate-500">
            Jumlah nota kasir tercetak
          </div>
          <div
            class="mt-3 h-[2px] rounded-full bg-gradient-to-r from-retro-primary to-transparent dark:from-retro-primary opacity-40"
          ></div>
        </div>

        <!-- Card 3: Rata-Rata Transaksi -->
        <div
          class="kpi-card group bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 border border-slate-200 dark:border-retro-accent/20 rounded-xl p-5 transition-all duration-300 hover:shadow-lg dark:hover:shadow-retro-accent/5 hover:-translate-y-0.5"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center bg-retro-accent/10 dark:bg-retro-accent/20 text-retro-accent"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                  />
                </svg>
              </div>
              <span
                class="text-[11px] font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400"
                >Rata-Rata</span
              >
            </div>
          </div>
          <div class="text-xl font-bold tracking-tight text-slate-800 dark:text-slate-100">
            Rp {{ formatRupiah(avgTransaction) }}
          </div>
          <div class="text-[10px] mt-1.5 text-slate-400 dark:text-slate-500">
            Nilai rata-rata per transaksi
          </div>
          <div
            class="mt-3 h-[2px] rounded-full bg-gradient-to-r from-retro-accent to-transparent dark:from-retro-accent opacity-40"
          ></div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Daily Revenue Trend -->
        <div
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
        >
          <div
            class="px-5 py-4 flex justify-between items-center border-b border-slate-100 dark:border-slate-800"
          >
            <span class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-200"
              >Tren Penjualan Harian</span
            >
            <span
              class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-retro-accent/10 text-retro-accent"
              >7 Hari</span
            >
          </div>
          <div class="p-5 min-h-[260px] flex flex-col justify-between">
            <div
              v-if="trendData.length === 0"
              class="flex-1 flex items-center justify-center text-xs italic text-slate-400 dark:text-slate-500"
            >
              Tidak cukup data penjualan
            </div>
            <div
              v-else
              class="relative w-full flex-1 flex items-end justify-between pt-6 px-1 gap-1"
            >
              <!-- Gridlines -->
              <div class="absolute inset-0 flex flex-col justify-between pointer-events-none pb-8">
                <div class="w-full pt-1 border-b border-slate-100 dark:border-slate-800/50"></div>
                <div class="w-full border-b border-slate-100 dark:border-slate-800/50"></div>
                <div class="w-full border-b border-slate-100 dark:border-slate-800/50"></div>
                <div class="w-full border-b border-slate-100 dark:border-slate-800/50"></div>
              </div>

              <!-- Bars -->
              <div
                v-for="(day, idx) in trendData"
                :key="idx"
                class="flex-1 flex flex-col items-center group relative z-10"
              >
                <!-- Tooltip -->
                <div
                  class="absolute -top-8 px-2.5 py-1 rounded-md text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none whitespace-nowrap z-20 bg-slate-900 text-slate-100 border border-slate-800 shadow-xl"
                >
                  Rp {{ formatRupiah(day.total) }}
                </div>

                <!-- Bar -->
                <div
                  class="w-7 sm:w-9 rounded-md transition-all duration-500 ease-out relative group-hover:scale-x-110"
                  :style="{
                    height: `${day.percentage}%`,
                    background: `linear-gradient(to top, var(--color-primary) 0%, var(--color-primary-glow) 100%)`,
                    boxShadow: `0 0 12px var(--color-primary-glow)`,
                    border: `1px solid var(--color-primary-glow)`,
                  }"
                ></div>

                <!-- Date Label -->
                <span class="text-[9px] mt-2.5 font-medium text-slate-400 dark:text-slate-500">
                  {{ day.label }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Method Breakdowns -->
        <div
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
        >
          <div
            class="px-5 py-4 flex justify-between items-center border-b border-slate-100 dark:border-slate-800"
          >
            <span class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-200"
              >Metode Pembayaran</span
            >
            <span
              class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-retro-primary/10 text-retro-primary"
              >Breakdown</span
            >
          </div>
          <div class="p-5 flex-1 flex flex-col justify-center space-y-5">
            <div v-for="method in paymentMethods" :key="method.name" class="space-y-2">
              <div class="flex justify-between items-center">
                <span
                  class="text-xs font-semibold uppercase tracking-wide flex items-center gap-2 text-slate-700 dark:text-slate-300"
                >
                  <span
                    class="w-2 h-2 rounded-full"
                    :style="{ background: method.dotColor }"
                  ></span>
                  {{ method.name }}
                </span>
                <div class="flex items-center gap-2">
                  <span class="text-xs font-bold text-slate-800 dark:text-slate-100"
                    >{{ method.count }}x</span
                  >
                  <span
                    class="text-[10px] font-medium px-1.5 py-0.5 rounded"
                    :style="{ background: method.badgeBg, color: method.badgeText }"
                  >
                    {{ method.pct }}%
                  </span>
                </div>
              </div>
              <!-- Progress Bar -->
              <div
                class="relative w-full h-2 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800"
              >
                <div
                  class="h-full rounded-full transition-all duration-700 ease-out"
                  :style="{ width: `${method.pct}%`, background: method.barGradient }"
                ></div>
              </div>
              <div class="text-[10px] font-medium text-slate-400 dark:text-slate-500">
                Omzet: Rp {{ formatRupiah(method.total) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- STANDARD VIEW: Data Table (For non-owners or backup) -->
    <div
      v-else
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
    >
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">
        Memuat riwayat transaksi...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-800">
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Kode Transaksi
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Tanggal
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Operator Kasir
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Metode
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-right text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Total Belanja
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-center text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <tr
              v-for="t in transaksiList"
              :key="t.id"
              class="transition-colors duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800/50"
            >
              <td class="px-4 py-3 font-mono font-bold text-retro-primary dark:text-retro-primary">
                {{ t.kode_transaksi }}
              </td>
              <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                {{ formatDate(t.created_at) }}
              </td>
              <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">
                {{ t.nama_kasir || t.kasir?.name || '-' }}
              </td>
              <td class="px-4 py-3">
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
                >
                  {{ t.metode_pembayaran }}
                </span>
              </td>
              <td class="px-4 py-3 font-bold text-right text-slate-800 dark:text-slate-100">
                Rp {{ formatRupiah(t.total) }}
              </td>
              <td class="px-4 py-3 text-center flex items-center justify-center gap-1.5">
                <router-link
                  :to="`/transaksi/${t.id}`"
                  class="px-2.5 py-1 text-[10px] font-semibold rounded-md transition-all duration-200 bg-retro-primary/10 text-retro-primary border border-retro-primary/20 hover:bg-retro-primary/20"
                >
                  Detail
                </router-link>
                <button
                  @click="printTransaction(t.id)"
                  :disabled="printingId === t.id"
                  class="px-2.5 py-1 text-[10px] font-semibold rounded-md transition-all duration-200 bg-retro-orange/10 text-retro-orange border border-retro-orange/20 hover:bg-retro-orange/20 disabled:opacity-40"
                >
                  {{ printingId === t.id ? 'Loading...' : 'Cetak' }}
                </button>
                <button
                  v-if="isAdmin"
                  @click="deleteTransaction(t)"
                  class="px-2.5 py-1 text-[10px] font-semibold rounded-md transition-all duration-200 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800 hover:bg-rose-100 dark:hover:bg-rose-900/50"
                >
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="transaksiList.length === 0">
              <td
                colspan="6"
                class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500"
              >
                Belum ada transaksi terekam.
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
import type { Transaksi } from '@/types'
import { transaksiService, settingService } from '@/services'
import { useAuthStore } from '@/stores/auth'
import { printReceipt } from '@/utils/printReceipt'
import { customDialog } from '@/utils/dialog'
import { useTheme } from '@/utils/theme'

const { isDark } = useTheme()
const authStore = useAuthStore()
const loading = ref(true)
const transaksiList = ref<Transaksi[]>([])
const printingId = ref<number | null>(null)
const logoText = ref('Retro Komputer')

const isOwner = computed(() => authStore.isOwner)
const isAdmin = computed(() => authStore.isAdmin)

async function fetchTransactions() {
  loading.value = true
  try {
    const res = await transaksiService.getAll()
    transaksiList.value = res.data as Transaksi[]
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchTransactions()
  try {
    const settingRes = await settingService.getActive()
    if (settingRes.data && settingRes.data.logo_text) {
      logoText.value = settingRes.data.logo_text
    }
  } catch {
    // silent
  }
})

async function printTransaction(id: number) {
  printingId.value = id
  try {
    const res = await transaksiService.getById(id)
    if (res.data) {
      printReceipt(res.data, null, logoText.value)
    }
  } catch {
    customDialog.error('Gagal mengambil detail transaksi untuk dicetak.')
  } finally {
    printingId.value = null
  }
}

async function deleteTransaction(t: Transaksi) {
  const confirmed = await customDialog.confirm(
    `Apakah Anda yakin ingin menghapus transaksi ${t.kode_transaksi}? Tindakan ini akan mengembalikan stok produk dan membatalkan catatan keuangan!`,
  )
  if (!confirmed) {
    return
  }

  try {
    await transaksiService.delete(t.id)
    customDialog.success('Transaksi berhasil dihapus.')
    await fetchTransactions()
  } catch (err: unknown) {
    let msg = 'Gagal menghapus transaksi.'
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as Record<string, unknown>).response
      if (response && typeof response === 'object' && 'data' in response) {
        const data = response.data as Record<string, unknown>
        if (data.message) msg = String(data.message)
      }
    }
    customDialog.error(msg)
  }
}

// KPI Calculations
const totalRevenue = computed(() => {
  return transaksiList.value.reduce((acc, t) => acc + Number(t.total), 0)
})

const avgTransaction = computed(() => {
  if (transaksiList.value.length === 0) return 0
  return totalRevenue.value / transaksiList.value.length
})

// Payment Methods breakdown calculation
const paymentMethods = computed(() => {
  const methods = isDark.value
    ? [
        {
          name: 'tunai',
          count: 0,
          total: 0,
          dotColor: '#3b82f6',
          barGradient:
            'linear-gradient(90deg, rgba(59, 130, 246, 0.6) 0%, rgba(59, 130, 246, 0.3) 100%)',
          badgeBg: 'rgba(59, 130, 246, 0.1)',
          badgeText: '#3b82f6',
        },
        {
          name: 'debit',
          count: 0,
          total: 0,
          dotColor: '#60a5fa',
          barGradient:
            'linear-gradient(90deg, rgba(96, 165, 250, 0.6) 0%, rgba(96, 165, 250, 0.3) 100%)',
          badgeBg: 'rgba(96, 165, 250, 0.1)',
          badgeText: '#60a5fa',
        },
        {
          name: 'transfer',
          count: 0,
          total: 0,
          dotColor: '#FF7A00',
          barGradient:
            'linear-gradient(90deg, rgba(255, 122, 0, 0.6) 0%, rgba(255, 122, 0, 0.3) 100%)',
          badgeBg: 'rgba(255, 122, 0, 0.1)',
          badgeText: '#FF7A00',
        },
      ]
    : [
        {
          name: 'tunai',
          count: 0,
          total: 0,
          dotColor: '#FF7A00',
          barGradient:
            'linear-gradient(90deg, rgba(255, 122, 0, 0.6) 0%, rgba(255, 122, 0, 0.3) 100%)',
          badgeBg: 'rgba(255, 122, 0, 0.1)',
          badgeText: '#FF7A00',
        },
        {
          name: 'debit',
          count: 0,
          total: 0,
          dotColor: '#3b82f6',
          barGradient:
            'linear-gradient(90deg, rgba(59, 130, 246, 0.6) 0%, rgba(59, 130, 246, 0.3) 100%)',
          badgeBg: 'rgba(59, 130, 246, 0.1)',
          badgeText: '#3b82f6',
        },
        {
          name: 'transfer',
          count: 0,
          total: 0,
          dotColor: '#1D4ED8',
          barGradient:
            'linear-gradient(90deg, rgba(29, 78, 216, 0.6) 0%, rgba(29, 78, 216, 0.3) 100%)',
          badgeBg: 'rgba(29, 78, 216, 0.1)',
          badgeText: '#1D4ED8',
        },
      ]

  transaksiList.value.forEach((t) => {
    const match = methods.find((m) => m.name === t.metode_pembayaran.toLowerCase())
    if (match) {
      match.count++
      match.total += Number(t.total)
    }
  })

  const totalTrx = transaksiList.value.length || 1

  return methods.map((m) => ({
    ...m,
    pct: Math.round((m.count / totalTrx) * 100),
  }))
})

// Group by Date for SVG Daily Revenue trend chart (max 7 days)
const trendData = computed(() => {
  if (transaksiList.value.length === 0) return []

  // Sort and group
  const grouped: Record<string, number> = {}

  // Clone and sort transactions ascending by date
  const sorted = [...transaksiList.value].sort(
    (a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
  )

  sorted.forEach((t) => {
    const dateStr = new Date(t.created_at).toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
    })
    grouped[dateStr] = (grouped[dateStr] || 0) + Number(t.total)
  })

  // Take the last 7 dates
  const keys = Object.keys(grouped).slice(-7)
  const maxTotal = Math.max(...keys.map((k) => grouped[k] ?? 0), 1)

  return keys.map((k) => {
    const total = grouped[k] ?? 0
    return {
      label: k,
      total,
      percentage: Math.max(Math.round((total / maxTotal) * 100), 5), // ensure at least small visible line
    }
  })
})

function formatRupiah(val: number | string): string {
  if (val === undefined || val === null) return '0'
  return new Intl.NumberFormat('id-ID').format(Number(val))
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
