<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-1 h-6 rounded-full bg-gradient-to-b from-retro-accent to-retro-primary"></div>
        <h2
          class="text-sm font-semibold tracking-wider uppercase text-slate-800 dark:text-slate-200"
        >
          Laporan Penjualan
        </h2>
      </div>
      <button
        v-if="canDownload"
        @click="showDownloadModal = true"
        class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-white bg-retro-primary hover:bg-retro-primary-hover rounded-md transition-all shadow-md"
      >
        <svg
          class="w-4 h-4"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"
          />
        </svg>
        Unduh Laporan
      </button>
    </div>

    <!-- Filter Bar -->
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm p-5 space-y-4"
    >
      <h3
        class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide flex items-center gap-2"
      >
        <span class="inline-block w-1.5 h-1.5 bg-retro-primary rounded-full"></span>
        Filter Laporan
      </h3>
      <div class="flex flex-wrap items-end gap-3">
        <!-- Mode Filter -->
        <div class="flex flex-col gap-1">
          <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
            >Mode</label
          >
          <select
            v-model="filterMode"
            @change="onFilterModeChange"
            class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[160px] focus:ring-2 focus:ring-retro-primary/30"
          >
            <template v-if="authStore.isKasir">
              <option value="hari_ini">Hari Ini</option>
              <option value="minggu_ini">Minggu Ini</option>
              <option value="">Bulan Ini</option>
            </template>
            <template v-else>
              <optgroup label="Default">
                <option value="hari_ini">Hari Ini</option>
                <option value="minggu_ini">Minggu Ini</option>
                <option value="">Bulan Ini</option>
              </optgroup>
              <optgroup label="Periode">
                <option value="harian">Hari</option>
                <option value="mingguan">Minggu</option>
                <option value="bulanan">Bulan</option>
                <option value="tahunan">Tahun</option>
              </optgroup>
              <optgroup label="Spesifik">
                <option value="tanggal">Berdasarkan Tanggal</option>
                <option value="rentang">Rentang Waktu Khusus</option>
              </optgroup>
            </template>
          </select>
        </div>

        <!-- Sub-filters (Harian, Mingguan, Bulanan, Tanggal, Tahun, Rentang) -->
        <div v-if="filterMode === 'harian'" class="flex flex-col gap-1">
          <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
            >Pilih Hari</label
          >
          <select
            v-model="filterValue"
            @change="applyFilter"
            class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[130px]"
          >
            <option value="senin">Senin</option>
            <option value="selasa">Selasa</option>
            <option value="rabu">Rabu</option>
            <option value="kamis">Kamis</option>
            <option value="jumat">Jumat</option>
            <option value="sabtu">Sabtu</option>
            <option value="minggu">Minggu</option>
          </select>
        </div>

        <div v-if="filterMode === 'mingguan'" class="flex flex-col gap-1">
          <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
            >Pilih Minggu</label
          >
          <select
            v-model="filterValue"
            @change="applyFilter"
            class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[150px]"
          >
            <option value="1">Minggu ke-1</option>
            <option value="2">Minggu ke-2</option>
            <option value="3">Minggu ke-3</option>
            <option value="4">Minggu ke-4</option>
            <option value="5">Minggu ke-5</option>
          </select>
        </div>

        <div v-if="filterMode === 'bulanan'" class="flex flex-col gap-1">
          <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
            >Pilih Bulan</label
          >
          <select
            v-model="filterValue"
            @change="applyFilter"
            class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[140px]"
          >
            <option v-for="(name, idx) in monthNames" :key="idx" :value="String(idx + 1)">
              {{ name }}
            </option>
          </select>
        </div>

        <div v-if="filterMode === 'tanggal'" class="flex flex-col gap-1">
          <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
            >Pilih Tanggal</label
          >
          <input
            v-model="filterValue"
            type="date"
            @change="applyFilter"
            class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[160px]"
          />
        </div>

        <div v-if="filterMode === 'tahunan'" class="flex flex-col gap-1">
          <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
            >Pilih Tahun</label
          >
          <select
            v-model="filterValue"
            @change="applyFilter"
            class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[120px]"
          >
            <option v-for="y in availableYears" :key="y" :value="String(y)">{{ y }}</option>
          </select>
        </div>

        <template v-if="filterMode === 'rentang'">
          <div class="flex flex-col gap-1">
            <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
              >Dari Tanggal</label
            >
            <input
              v-model="filterStart"
              type="date"
              @change="applyFilter"
              class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[160px]"
            />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase"
              >Sampai Tanggal</label
            >
            <input
              v-model="filterEnd"
              type="date"
              @change="applyFilter"
              class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 min-w-[160px]"
            />
          </div>
        </template>

        <!-- Reset Button -->
        <button
          v-if="filterMode"
          @click="resetFilter"
          class="text-[11px] px-3 py-2 rounded-md border border-red-300 text-red-500 hover:bg-red-50 transition-all font-semibold"
        >
          ✕ Reset
        </button>
      </div>

      <!-- Active Filter Badge -->
      <div
        v-if="filterMode"
        class="pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center gap-2"
      >
        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider"
          >Filter Aktif:</span
        >
        <span
          class="text-xs px-3 py-1 rounded-full bg-retro-primary/10 text-retro-primary font-semibold border border-retro-primary/30"
        >
          {{ activeFilterLabel }}
        </span>
      </div>
    </div>

    <!-- Table Container -->
    <div
      class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
    >
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 dark:text-slate-500">
        Memuat data laporan...
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-800">
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Kode
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Tanggal
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Metode
              </th>
              <th
                class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-right text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Total
              </th>
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
                <span
                  class="px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700"
                >
                  {{ t.metode_pembayaran }}
                </span>
              </td>
              <td class="px-4 py-3 font-bold text-right text-slate-800 dark:text-slate-100">
                {{ formatCurrency(t.total) }}
              </td>
            </tr>
            <tr v-if="list.length === 0">
              <td
                colspan="4"
                class="px-4 py-8 text-center text-sm text-slate-400 dark:text-slate-500"
              >
                Belum ada data
              </td>
            </tr>
          </tbody>
          <tfoot v-if="list.length > 0">
            <tr
              class="bg-slate-50/80 dark:bg-slate-950/40 border-t border-slate-200 dark:border-slate-800"
            >
              <td
                colspan="3"
                class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300"
              >
                Total
              </td>
              <td class="px-4 py-3 text-xs font-bold text-right font-mono text-retro-primary">
                {{ formatCurrency(grandTotal) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Modal Unduh Laporan -->
    <div
      v-if="showDownloadModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
      <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 w-full max-w-md rounded-xl p-6 shadow-2xl space-y-4 text-slate-800 dark:text-slate-200"
      >
        <div
          class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3"
        >
          <h3 class="text-sm font-bold uppercase tracking-wider">Unduh Laporan Penjualan</h3>
          <button
            @click="showDownloadModal = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
          >
            ✕
          </button>
        </div>

        <div class="space-y-3">
          <!-- Opsi Periode -->
          <div class="flex flex-col gap-1">
            <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400"
              >Pilih Periode</label
            >
            <select
              v-model="downloadPeriod"
              class="text-xs rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-3 py-2 focus:ring-2 focus:ring-retro-primary/30"
            >
              <option value="active">Sesuai Filter Aktif ({{ activeFilterLabel }})</option>
              <option value="mingguan">Mingguan (7 Hari Terakhir)</option>
              <option value="bulanan">Bulanan (Bulan Ini)</option>
              <option value="tahunan">Tahunan (Tahun Ini)</option>
            </select>
          </div>

          <!-- Opsi Format -->
          <div class="flex flex-col gap-1">
            <label class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400"
              >Format Laporan</label
            >
            <div class="flex gap-4 mt-1">
              <label class="flex items-center gap-2 cursor-pointer text-xs">
                <input
                  type="radio"
                  v-model="downloadFormat"
                  value="excel"
                  class="text-retro-primary focus:ring-retro-primary"
                />
                Excel (.xlsx)
              </label>
              <label class="flex items-center gap-2 cursor-pointer text-xs">
                <input
                  type="radio"
                  v-model="downloadFormat"
                  value="pdf"
                  class="text-retro-primary focus:ring-retro-primary"
                />
                PDF (.pdf)
              </label>
            </div>
          </div>
        </div>

        <div
          class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800"
        >
          <button
            @click="showDownloadModal = false"
            class="px-4 py-2 text-xs font-semibold rounded-md border border-slate-200 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800"
          >
            Batal
          </button>
          <button
            @click="handleDownload"
            class="px-4 py-2 text-xs font-bold text-white bg-retro-primary hover:bg-retro-primary-hover rounded-md transition-all shadow-md flex items-center gap-2"
          >
            <span>Unduh</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import type { Transaksi, DashboardFilterParams } from '@/types'
import { transaksiService } from '@/services'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const loading = ref(true)
const list = ref<Transaksi[]>([])
const grandTotal = computed(() => list.value.reduce((s, t) => s + Number(t.total), 0))

// ===== Download State =====
const showDownloadModal = ref(false)
const downloadPeriod = ref<'active' | 'mingguan' | 'bulanan' | 'tahunan'>('active')
const downloadFormat = ref<'excel' | 'pdf'>('excel')

// ===== Filter State =====
const filterMode = ref<string>('')
const filterValue = ref<string>('')
const filterStart = ref<string>('')
const filterEnd = ref<string>('')

// Check if user is allowed to download Excel
const canDownload = computed(() => authStore.isAdmin || authStore.isOwner)

// Daftar tahun untuk filter mode "Tahun" (5 tahun terakhir)
const availableYears = computed(() => {
  const y = new Date().getFullYear()
  return [y, y - 1, y - 2, y - 3, y - 4]
})

const monthNames = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
]

const dayNames: Record<string, string> = {
  senin: 'Senin',
  selasa: 'Selasa',
  rabu: 'Rabu',
  kamis: 'Kamis',
  jumat: 'Jumat',
  sabtu: 'Sabtu',
  minggu: 'Minggu',
}

// Format tanggal pendek untuk label rentang
function formatShortDate(d: string): string {
  if (!d) return ''
  return new Date(d).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

// ===== Computed: active filter label =====
const activeFilterLabel = computed(() => {
  if (!filterMode.value) return 'Semua Periode'
  switch (filterMode.value) {
    case 'hari_ini':
      return 'Hari Ini'
    case 'minggu_ini':
      return 'Minggu Ini'
    case 'harian':
      return `Hari ${dayNames[filterValue.value] || filterValue.value}`
    case 'mingguan':
      return `Minggu ke-${filterValue.value}`
    case 'bulanan':
      return monthNames[parseInt(filterValue.value) - 1] || filterValue.value
    case 'tahunan':
      return `Tahun ${filterValue.value}`
    case 'tanggal':
      if (filterValue.value) {
        return new Date(filterValue.value).toLocaleDateString('id-ID', {
          day: '2-digit',
          month: 'long',
          year: 'numeric',
        })
      }
      return 'Pilih tanggal'
    case 'rentang':
      if (filterStart.value && filterEnd.value) {
        return `${formatShortDate(filterStart.value)} – ${formatShortDate(filterEnd.value)}`
      }
      return 'Pilih rentang'
    default:
      return 'Semua Periode'
  }
})

// ===== Filter Logic =====
function buildFilterParams(): DashboardFilterParams | undefined {
  if (!filterMode.value) return undefined

  if (filterMode.value === 'hari_ini' || filterMode.value === 'minggu_ini') {
    return {
      filter_mode: filterMode.value as DashboardFilterParams['filter_mode'],
      filter_year: new Date().getFullYear(),
    }
  }

  if (filterMode.value === 'rentang') {
    if (!filterStart.value || !filterEnd.value) return undefined
    return {
      filter_mode: 'rentang',
      filter_value: `${filterStart.value},${filterEnd.value}`,
      filter_year: new Date().getFullYear(),
    }
  }

  if (!filterValue.value) return undefined
  return {
    filter_mode: filterMode.value as DashboardFilterParams['filter_mode'],
    filter_value: filterValue.value,
    filter_year: new Date().getFullYear(),
  }
}

function onFilterModeChange() {
  switch (filterMode.value) {
    case 'harian':
      filterValue.value = 'senin'
      break
    case 'mingguan':
      filterValue.value = '1'
      break
    case 'bulanan':
      filterValue.value = String(new Date().getMonth() + 1)
      break
    case 'tahunan':
      filterValue.value = String(new Date().getFullYear())
      break
    case 'tanggal':
      filterValue.value = new Date().toISOString().split('T')[0] || ''
      break
    case 'rentang': {
      const today = new Date()
      const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
      filterStart.value = firstDay.toISOString().split('T')[0] || ''
      filterEnd.value = today.toISOString().split('T')[0] || ''
      filterValue.value = ''
      break
    }
    default:
      filterValue.value = ''
      break
  }
  applyFilter()
}

function resetFilter() {
  filterMode.value = ''
  filterValue.value = ''
  filterStart.value = ''
  filterEnd.value = ''
  applyFilter()
}

async function applyFilter() {
  loading.value = true
  try {
    const params = buildFilterParams()
    const res = await transaksiService.getAll(params)
    list.value = res.data as Transaksi[]
  } catch (err) {
    console.error('Gagal mengambil laporan penjualan:', err)
  } finally {
    loading.value = false
  }
}

async function handleDownload() {
  showDownloadModal.value = false

  let params: DashboardFilterParams = {}

  if (downloadPeriod.value === 'active') {
    const activeParams = buildFilterParams()
    if (activeParams) {
      params = activeParams
    }
  } else if (downloadPeriod.value === 'mingguan') {
    params = {
      filter_mode: 'minggu_ini',
      filter_year: new Date().getFullYear(),
    }
  } else if (downloadPeriod.value === 'bulanan') {
    params = {
      filter_mode: 'bulan_ini',
      filter_year: new Date().getFullYear(),
    }
  } else if (downloadPeriod.value === 'tahunan') {
    params = {
      filter_mode: 'tahunan',
      filter_value: String(new Date().getFullYear()),
      filter_year: new Date().getFullYear(),
    }
  }

  if (downloadFormat.value === 'excel') {
    await triggerExcelDownload(params)
  } else {
    await triggerPdfDownload(params)
  }
}

async function triggerExcelDownload(params: DashboardFilterParams) {
  const token = localStorage.getItem('auth_token') ?? ''
  const url = new URL('/api/laporan/export-excel', window.location.origin)
  url.searchParams.set('type', 'transaksi')

  if (params.filter_mode) {
    url.searchParams.set('filter_mode', params.filter_mode)
  }
  if (params.filter_value) {
    url.searchParams.set('filter_value', params.filter_value)
  }
  if (params.filter_year) {
    url.searchParams.set('filter_year', String(params.filter_year))
  }
  if (token) {
    url.searchParams.set('token', token)
  }

  try {
    const res = await fetch(url.toString())
    if (res.status === 401 || res.status === 403) {
      alert('Anda tidak memiliki wewenang untuk mendownload laporan ini.')
      return
    }
    const disposition = res.headers.get('Content-Disposition') ?? ''
    const match = disposition.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? `Laporan_Penjualan.xlsx`
    const blob = await res.blob()
    const a = document.createElement('a')
    a.href = URL.createObjectURL(blob)
    a.download = filename
    a.click()
    URL.revokeObjectURL(a.href)
  } catch (e) {
    console.error('Download Excel failed:', e)
  }
}

async function triggerPdfDownload(params: DashboardFilterParams) {
  loading.value = true
  try {
    const res = await transaksiService.getAll(params)
    const transactions = res.data as Transaksi[]
    const totalAmount = transactions.reduce((sum, t) => sum + Number(t.total), 0)

    const printWindow = window.open('', '_blank')
    if (!printWindow) {
      alert('Gagal membuka jendela cetak. Pastikan pop-up diizinkan.')
      return
    }

    const title = 'Laporan Rincian Transaksi Penjualan'
    let periodLabel = 'Semua Periode'
    if (downloadPeriod.value === 'active') {
      periodLabel = activeFilterLabel.value
    } else if (downloadPeriod.value === 'mingguan') {
      periodLabel = 'Minggu Ini (7 Hari Terakhir)'
    } else if (downloadPeriod.value === 'bulanan') {
      periodLabel = 'Bulan Ini'
    } else if (downloadPeriod.value === 'tahunan') {
      periodLabel = 'Tahun Ini'
    }

    const rowsHtml = transactions
      .map(
        (t, idx) => `
      <tr>
        <td style="text-align: center;">${idx + 1}</td>
        <td style="font-family: monospace; font-weight: bold; color: #1d4ed8;">${t.kode_transaksi}</td>
        <td>${new Date(t.created_at).toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</td>
        <td style="text-align: center; font-weight: 600;">${t.metode_pembayaran.toUpperCase()}</td>
        <td style="text-align: right; font-weight: 600;">${formatCurrency(Number(t.total))}</td>
      </tr>
    `,
      )
      .join('')

    printWindow.document.write(`
      <html>
        <head>
          <title>${title}</title>
          <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            body {
              font-family: 'Inter', sans-serif;
              color: #1e293b;
              padding: 40px;
              margin: 0;
              background-color: #ffffff;
            }
            .header {
              text-align: center;
              margin-bottom: 30px;
              border-bottom: 2px solid #ff7a00;
              padding-bottom: 20px;
            }
            .logo-text {
              font-size: 24px;
              font-weight: 800;
              color: #0b0f19;
              letter-spacing: 1px;
            }
            .logo-text span {
              color: #ff7a00;
            }
            .doc-title {
              font-size: 16px;
              font-weight: 700;
              text-transform: uppercase;
              color: #475569;
              margin-top: 5px;
            }
            .meta-info {
              display: flex;
              justify-content: space-between;
              margin-bottom: 20px;
              font-size: 11px;
              color: #64748b;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin-top: 15px;
            }
            thead {
              display: table-header-group;
            }
            tr {
              page-break-inside: avoid;
              break-inside: avoid;
            }
            th {
              background-color: #ff7a00;
              color: #ffffff;
              padding: 10px 12px;
              font-size: 11px;
              font-weight: 700;
              text-transform: uppercase;
              border: 1px solid #cc6200;
              letter-spacing: 0.5px;
            }
            td {
              padding: 10px 12px;
              font-size: 11px;
              border: 1px solid #e2e8f0;
              color: #334155;
            }
            tr:nth-child(even) td {
              background-color: #f8fafc;
            }
            .total-row td {
              font-weight: 800;
              background-color: #f1f5f9 !important;
              border-top: 2px solid #cbd5e1;
              color: #0b0f19;
            }
            @media print {
              body { padding: 20px; }
              .no-print { display: none; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <div class="logo-text">RETRO<span>KOMPUTER</span></div>
            <div class="doc-title">${title}</div>
          </div>

          <div class="meta-info">
            <div><strong>Periode Laporan:</strong> ${periodLabel}</div>
            <div><strong>Dicetak Pada:</strong> ${new Date().toLocaleString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</div>
          </div>

          <table>
            <thead>
              <tr>
                <th style="width: 50px; text-align: center;">No</th>
                <th>Kode Transaksi</th>
                <th>Tanggal Transaksi</th>
                <th style="width: 120px; text-align: center;">Metode Pembayaran</th>
                <th style="width: 150px; text-align: right;">Total Nominal</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
              <tr class="total-row">
                <td colspan="4" style="text-align: right; text-transform: uppercase; letter-spacing: 0.5px;">Grand Total</td>
                <td style="text-align: right;">${formatCurrency(totalAmount)}</td>
              </tr>
            </tbody>
          </table>

          <script>
            window.onload = function() {
              window.print();
              setTimeout(function() { window.close(); }, 500);
            };
          <\/script>
        </body>
      </html>
    `)
    printWindow.document.close()
  } catch (err) {
    console.error('Cetak PDF gagal:', err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const res = await transaksiService.getAll()
    list.value = res.data as Transaksi[]
  } catch {
  } finally {
    loading.value = false
  }
})

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}
function formatDate(d: string) {
  return new Date(d).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
