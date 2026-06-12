<template>
  <div class="owner-dashboard">
    <!-- ============ HEADER ============ -->
    <div class="dashboard-header">
      <div>
        <h1 class="dashboard-title">Dashboard Owner</h1>
        <p class="dashboard-subtitle">Pantau performa bisnis Anda secara real-time</p>
      </div>
      <div class="header-date">
        {{ currentDate }}
      </div>
    </div>

    <!-- ============ FILTER BAR ============ -->
    <div class="filter-bar-card">
      <h3 class="filter-title">
        <span class="filter-bullet"></span>
        Filter Data Analitis
      </h3>
      <div class="filter-inputs">
        <!-- Mode Filter -->
        <div class="filter-group">
          <label class="filter-label">Mode</label>
          <select v-model="filterMode" @change="onFilterModeChange" class="filter-select">
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
          </select>
        </div>

        <!-- Sub-filter: Harian -->
        <div v-if="filterMode === 'harian'" class="filter-group">
          <label class="filter-label">Pilih Hari</label>
          <select v-model="filterValue" @change="applyFilter" class="filter-select">
            <option value="senin">Senin</option>
            <option value="selasa">Selasa</option>
            <option value="rabu">Rabu</option>
            <option value="kamis">Kamis</option>
            <option value="jumat">Jumat</option>
            <option value="sabtu">Sabtu</option>
            <option value="minggu">Minggu</option>
          </select>
        </div>

        <!-- Sub-filter: Mingguan -->
        <div v-if="filterMode === 'mingguan'" class="filter-group">
          <label class="filter-label">Pilih Minggu</label>
          <select v-model="filterValue" @change="applyFilter" class="filter-select">
            <option value="1">Minggu ke-1</option>
            <option value="2">Minggu ke-2</option>
            <option value="3">Minggu ke-3</option>
            <option value="4">Minggu ke-4</option>
            <option value="5">Minggu ke-5</option>
          </select>
        </div>

        <!-- Sub-filter: Bulanan -->
        <div v-if="filterMode === 'bulanan'" class="filter-group">
          <label class="filter-label">Pilih Bulan</label>
          <select v-model="filterValue" @change="applyFilter" class="filter-select">
            <option v-for="(name, idx) in monthNames" :key="idx" :value="String(idx + 1)">
              {{ name }}
            </option>
          </select>
        </div>

        <!-- Sub-filter: Tanggal -->
        <div v-if="filterMode === 'tanggal'" class="filter-group">
          <label class="filter-label">Pilih Tanggal</label>
          <input
            v-model="filterValue"
            type="date"
            @change="applyFilter"
            class="filter-input-date"
          />
        </div>

        <!-- Sub-filter: Tahun -->
        <div v-if="filterMode === 'tahunan'" class="filter-group">
          <label class="filter-label">Pilih Tahun</label>
          <select v-model="filterValue" @change="applyFilter" class="filter-select">
            <option v-for="y in availableYears" :key="y" :value="String(y)">{{ y }}</option>
          </select>
        </div>

        <!-- Sub-filter: Rentang -->
        <template v-if="filterMode === 'rentang'">
          <div class="filter-group">
            <label class="filter-label">Dari Tanggal</label>
            <input
              v-model="filterStart"
              type="date"
              @change="applyFilter"
              class="filter-input-date"
            />
          </div>
          <div class="filter-group">
            <label class="filter-label">Sampai Tanggal</label>
            <input
              v-model="filterEnd"
              type="date"
              @change="applyFilter"
              class="filter-input-date"
            />
          </div>
        </template>

        <!-- Reset Button -->
        <button v-if="filterMode" @click="resetFilter" class="btn-reset-filter">✕ Reset</button>
      </div>

      <!-- Active Filter Badge -->
      <div v-if="filterMode" class="active-filter-row">
        <span class="active-filter-title">Filter Aktif:</span>
        <span class="active-filter-badge">
          {{ activeFilterLabel }}
        </span>
      </div>
    </div>

    <!-- ============ KPI STATS CARDS ============ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
      <!-- Penjualan -->
      <div
        class="bg-gradient-to-br from-retro-success-glow to-white dark:from-retro-success-glow dark:to-slate-800 rounded-lg border border-retro-success/20 dark:border-retro-success/30 p-3 md:p-5 hover:shadow-lg transition-all hover:border-retro-success/50 dark:hover:border-retro-success-hover min-h-[120px] md:min-h-[140px] flex flex-col justify-between"
      >
        <div>
          <p
            class="text-[10px] md:text-xs text-retro-success font-bold uppercase tracking-wider mb-1 md:mb-2 line-clamp-1"
          >
            {{ kpiPenjualanLabel }}
          </p>
          <p class="text-xl md:text-3xl font-bold text-retro-success mb-1 line-clamp-2 break-words">
            {{ formatCurrency(stats.penjualan_bulan_ini) }}
          </p>
        </div>
        <p class="text-[10px] md:text-xs text-retro-success font-medium">
          <span v-if="percentChange.penjualan !== null && !filterMode">
            {{ percentChange.penjualan >= 0 ? '↑' : '↓' }}
            {{ Math.abs(percentChange.penjualan).toFixed(1) }}% vs bulan lalu
          </span>
          <span v-else>{{ kpiSublabel }}</span>
        </p>
      </div>

      <!-- Laba Bersih -->
      <div
        :class="[
          stats.laba_bersih >= 0
            ? 'bg-gradient-to-br from-retro-success-glow to-white dark:from-retro-success-glow dark:to-slate-800 border border-retro-success/20 dark:border-retro-success/30 hover:border-retro-success/50 dark:hover:border-retro-success-hover'
            : 'bg-gradient-to-br from-red-50 to-white dark:from-red-900/20 dark:to-slate-800 border border-red-200/50 dark:border-red-800/50 hover:border-red-300 dark:hover:border-red-700',
          'rounded-lg p-3 md:p-5 hover:shadow-lg transition-all min-h-[120px] md:min-h-[140px] flex flex-col justify-between',
        ]"
      >
        <div>
          <p
            class="text-[10px] md:text-xs font-bold uppercase tracking-wider mb-1 md:mb-2 line-clamp-1"
            :class="
              stats.laba_bersih >= 0 ? 'text-retro-success' : 'text-red-600 dark:text-red-400'
            "
          >
            Laba Bersih
          </p>
          <p
            class="text-xl md:text-3xl font-bold mb-1 line-clamp-2 break-words"
            :class="
              stats.laba_bersih >= 0 ? 'text-retro-success' : 'text-red-700 dark:text-red-300'
            "
          >
            {{ formatCurrency(stats.laba_bersih) }}
          </p>
        </div>
        <p
          class="text-[10px] md:text-xs font-medium"
          :class="
            stats.laba_bersih >= 0 ? 'text-retro-success' : 'text-red-500 dark:text-red-500/80'
          "
        >
          <span v-if="percentChange.laba !== null && !filterMode">
            {{ percentChange.laba >= 0 ? '↑' : '↓' }} {{ Math.abs(percentChange.laba).toFixed(1) }}%
            vs bulan lalu
          </span>
          <span v-else>{{ stats.laba_bersih >= 0 ? 'Keuntungan bersih' : 'Kerugian bersih' }}</span>
        </p>
      </div>

      <!-- Total Transaksi -->
      <div
        class="bg-gradient-to-br from-blue-50 to-white dark:from-blue-900/20 dark:to-slate-800 rounded-lg border border-blue-200/50 dark:border-blue-800/50 p-3 md:p-5 hover:shadow-lg transition-all hover:border-blue-300 dark:hover:border-blue-700 min-h-[120px] md:min-h-[140px] flex flex-col justify-between"
      >
        <div>
          <p
            class="text-[10px] md:text-xs text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider mb-1 md:mb-2 line-clamp-1"
          >
            Total Transaksi
          </p>
          <p
            class="text-xl md:text-3xl font-bold text-blue-700 dark:text-blue-300 mb-1 line-clamp-2"
          >
            {{ stats.total_transaksi }}
          </p>
        </div>
        <p class="text-[10px] md:text-xs text-blue-500 dark:text-blue-500/80 font-medium">
          <span v-if="percentChange.transaksi !== null && !filterMode">
            {{ percentChange.transaksi >= 0 ? '↑' : '↓' }}
            {{ Math.abs(percentChange.transaksi).toFixed(1) }}% vs bulan lalu
          </span>
          <span v-else>{{ kpiSublabel }}</span>
        </p>
      </div>

      <!-- Pembelian -->
      <div
        class="bg-gradient-to-br from-orange-50 to-white dark:from-orange-900/20 dark:to-slate-800 rounded-lg border border-orange-200/50 dark:border-orange-800/50 p-3 md:p-5 hover:shadow-lg transition-all hover:border-orange-300 dark:hover:border-orange-700 min-h-[120px] md:min-h-[140px] flex flex-col justify-between"
      >
        <div>
          <p
            class="text-[10px] md:text-xs text-orange-600 dark:text-orange-400 font-bold uppercase tracking-wider mb-1 md:mb-2 line-clamp-1"
          >
            {{ kpiPembelianLabel }}
          </p>
          <p
            class="text-xl md:text-3xl font-bold text-orange-700 dark:text-orange-300 mb-1 line-clamp-2 break-words"
          >
            {{ formatCurrency(stats.pembelian_bulan_ini) }}
          </p>
        </div>
        <p class="text-[10px] md:text-xs text-orange-500 dark:text-orange-500/80 font-medium">
          <span v-if="percentChange.pembelian !== null && !filterMode">
            {{ percentChange.pembelian <= 0 ? '↓' : '↑' }}
            {{ Math.abs(percentChange.pembelian).toFixed(1) }}% vs bulan lalu
          </span>
          <span v-else>Dari supplier</span>
        </p>
      </div>
    </div>

    <!-- ============ TABS FILTER ============ -->
    <div class="chart-tabs">
      <button :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">
        <svg
          class="tab-icon"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <rect x="3" y="3" width="7" height="9" />
          <rect x="14" y="3" width="7" height="5" />
          <rect x="14" y="12" width="7" height="9" />
          <rect x="3" y="16" width="7" height="5" />
        </svg>
        Semua Grafik
      </button>
      <button :class="{ active: activeTab === 'sales' }" @click="activeTab = 'sales'">
        <svg
          class="tab-icon"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
          <polyline points="17 6 23 6 23 12" />
        </svg>
        Tren Penjualan
      </button>
      <button :class="{ active: activeTab === 'profit' }" @click="activeTab = 'profit'">
        <svg
          class="tab-icon"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <line x1="12" y1="1" x2="12" y2="23" />
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
        </svg>
        Laba Kotor
      </button>
      <button :class="{ active: activeTab === 'products' }" @click="activeTab = 'products'">
        <svg
          class="tab-icon"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <circle cx="12" cy="8" r="7" />
          <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88" />
        </svg>
        Produk & Pembayaran
      </button>
    </div>

    <!-- ============ TABS CONTENT ============ -->
    <div class="tabs-content">
      <!-- Tren Penjualan Harian (Sales/All) -->
      <div v-if="activeTab === 'all' || activeTab === 'sales'" class="chart-container-full mb-5">
        <SalesLineChart />
      </div>

      <!-- Row: Monthly Sales & Payment Donut (All) -->
      <div v-if="activeTab === 'all'" class="chart-row mb-5">
        <div class="chart-col-large">
          <MonthlySalesBar />
        </div>
        <div class="chart-col-small">
          <PaymentDonut />
        </div>
      </div>

      <!-- Specific Sales Tab: show Monthly Sales Bar full width -->
      <div v-else-if="activeTab === 'sales'" class="chart-container-full">
        <MonthlySalesBar />
      </div>

      <!-- Laba & Rugi Tab (Profit/All) -->
      <div v-if="activeTab === 'all' || activeTab === 'profit'" class="chart-container-full mb-5">
        <ProfitAreaChart />
      </div>

      <!-- Specific Products Tab: show Payment Donut & Top Products Bar in a row -->
      <div v-if="activeTab === 'products'" class="chart-row">
        <div class="chart-col-medium">
          <PaymentDonut />
        </div>
        <div class="chart-col-medium">
          <TopProductsBar />
        </div>
      </div>

      <!-- Top Products Bar (All) -->
      <div v-if="activeTab === 'all'" class="chart-row">
        <div class="chart-col-large">
          <!-- spacer kosong; chart tidak dirender agar tidak fetch data sia-sia -->
        </div>
        <div class="chart-col-small">
          <TopProductsBar />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import type { DashboardStats, ChartPenjualanBulanan, DashboardFilterParams } from '@/types'
import { laporanService } from '@/services'

// Chart Components
import SalesLineChart from '@/components/charts/SalesLineChart.vue'
import MonthlySalesBar from '@/components/charts/MonthlySalesBar.vue'
import PaymentDonut from '@/components/charts/PaymentDonut.vue'
import TopProductsBar from '@/components/charts/TopProductsBar.vue'
import ProfitAreaChart from '@/components/charts/ProfitAreaChart.vue'

const loading = ref(true)
const activeTab = ref('all')
const stats = ref<DashboardStats>({
  penjualan_bulan_ini: 0,
  pembelian_bulan_ini: 0,
  laba_bersih: 0,
  total_transaksi: 0,
  kerugian_inventaris: 0,
})

const percentChange = reactive<{
  penjualan: number | null
  laba: number | null
  transaksi: number | null
  pembelian: number | null
}>({
  penjualan: null,
  laba: null,
  transaksi: null,
  pembelian: null,
})

const currentDate = new Date().toLocaleDateString('id-ID', {
  weekday: 'long',
  day: '2-digit',
  month: 'long',
  year: 'numeric',
})

// ===== Filter State =====
const filterMode = ref<string>('')
const filterValue = ref<string>('')
const filterStart = ref<string>('')
const filterEnd = ref<string>('')

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
  if (!filterMode.value) return 'Bulan Ini'
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
      return 'Bulan Ini'
  }
})

// ===== KPI Labels =====
const kpiPenjualanLabel = computed(() => {
  if (!filterMode.value) return 'Penjualan Bulan Ini'
  switch (filterMode.value) {
    case 'hari_ini':
      return 'Penjualan Hari Ini'
    case 'minggu_ini':
      return 'Penjualan Minggu Ini'
    case 'harian':
      return `Penjualan Hari ${dayNames[filterValue.value] || ''}`
    case 'mingguan':
      return `Penjualan Minggu ke-${filterValue.value}`
    case 'bulanan':
      return `Penjualan ${monthNames[parseInt(filterValue.value) - 1] || ''}`
    case 'tahunan':
      return `Penjualan Tahun ${filterValue.value}`
    case 'tanggal':
      if (filterValue.value) {
        return `Penjualan ${new Date(filterValue.value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })}`
      }
      return 'Penjualan Tanggal'
    case 'rentang':
      return 'Penjualan Rentang'
    default:
      return 'Penjualan Bulan Ini'
  }
})

const kpiPembelianLabel = computed(() => {
  if (!filterMode.value) return 'Pembelian Bulan Ini'
  switch (filterMode.value) {
    case 'hari_ini':
      return 'Pembelian Hari Ini'
    case 'minggu_ini':
      return 'Pembelian Minggu Ini'
    case 'harian':
      return `Pembelian Hari ${dayNames[filterValue.value] || ''}`
    case 'mingguan':
      return `Pembelian Minggu ke-${filterValue.value}`
    case 'bulanan':
      return `Pembelian ${monthNames[parseInt(filterValue.value) - 1] || ''}`
    case 'tahunan':
      return `Pembelian Tahun ${filterValue.value}`
    case 'tanggal':
      if (filterValue.value) {
        return `Pembelian ${new Date(filterValue.value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })}`
      }
      return 'Pembelian Tanggal'
    case 'rentang':
      return 'Pembelian Rentang'
    default:
      return 'Pembelian Bulan Ini'
  }
})

const kpiSublabel = computed(() => {
  if (!filterMode.value) return 'Data bulan ini'
  switch (filterMode.value) {
    case 'hari_ini':
      return 'Data hari ini'
    case 'minggu_ini':
      return 'Data minggu ini'
    case 'harian':
      return `Setiap hari ${dayNames[filterValue.value] || ''} bulan ini`
    case 'mingguan':
      return `Minggu ke-${filterValue.value} bulan ini`
    case 'bulanan':
      return `Bulan ${monthNames[parseInt(filterValue.value) - 1] || ''}`
    case 'tahunan':
      return `Tahun ${filterValue.value}`
    case 'tanggal':
      return `Tanggal ${filterValue.value}`
    case 'rentang':
      return filterStart.value && filterEnd.value
        ? `${formatShortDate(filterStart.value)} – ${formatShortDate(filterEnd.value)}`
        : 'Rentang tanggal'
    default:
      return 'Data bulan ini'
  }
})

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}

function calcPercentChange(current: number, previous: number): number | null {
  if (previous === 0 && current === 0) return null
  if (previous === 0) return 100
  return ((current - previous) / Math.abs(previous)) * 100
}

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
    const dashRes = await laporanService.getDashboard(params)
    stats.value = dashRes.data
  } catch (e) {
    console.error('Gagal memfilter dashboard:', e)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const [dashRes, monthlyRes] = await Promise.all([
      laporanService.getDashboard(),
      laporanService.getChartPenjualanBulanan(),
    ])

    stats.value = dashRes.data
    const monthlyData = monthlyRes.data as ChartPenjualanBulanan[]

    // Calculate % change vs previous month
    if (monthlyData.length >= 2) {
      const current = monthlyData[monthlyData.length - 1]!
      const previous = monthlyData[monthlyData.length - 2]!

      percentChange.penjualan = calcPercentChange(current.total_penjualan, previous.total_penjualan)
      percentChange.laba = calcPercentChange(current.laba_bersih, previous.laba_bersih)
      percentChange.pembelian = calcPercentChange(current.total_pembelian, previous.total_pembelian)
      // Transaksi count not available in monthly data, use penjualan as proxy
      percentChange.transaksi = calcPercentChange(current.total_penjualan, previous.total_penjualan)
    }
  } catch (e) {
    console.error('Failed to load owner dashboard:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
/* ===== Dashboard Layout ===== */
.owner-dashboard {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 1280px;
  margin: 0 auto;
}

/* ===== Filter Bar Card ===== */
.filter-bar-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.05),
    0 2px 4px -1px rgba(0, 0, 0, 0.03);
  transition: all 0.3s ease;
}

.dark .filter-bar-card {
  background: #131926;
  border-color: var(--color-primary-glow);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.filter-title {
  font-size: 13px;
  font-weight: 700;
  color: #1e293b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 16px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dark .filter-title {
  color: #f8fafc;
}

.filter-bullet {
  display: inline-block;
  width: 8px;
  height: 8px;
  background-color: var(--color-primary);
  border-radius: 9999px;
}

.filter-inputs {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 12px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-label {
  font-size: 10px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.dark .filter-label {
  color: #94a3b8;
}

.filter-select,
.filter-input-date {
  font-size: 12px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #1e293b;
  padding: 8px 12px;
  min-width: 150px;
  outline: none;
  transition: all 0.2s ease;
}

.dark .filter-select,
.dark .filter-input-date {
  border-color: var(--color-primary-glow);
  background: #0b0f19;
  color: #f8fafc;
}

.filter-select:focus,
.filter-input-date:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 8px var(--color-primary-glow);
}

.btn-reset-filter {
  font-size: 11px;
  font-weight: 700;
  color: #f43f5e;
  border: 1px solid rgba(244, 63, 94, 0.2);
  background: transparent;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-filter:hover {
  background: rgba(244, 63, 94, 0.1);
  border-color: #f43f5e;
  box-shadow: 0 0 8px rgba(244, 63, 94, 0.2);
}

.active-filter-row {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dark .active-filter-row {
  border-top-color: var(--color-primary-glow);
}

.active-filter-title {
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
}

.dark .active-filter-title {
  color: #94a3b8;
}

.active-filter-badge {
  font-size: 11px;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--color-primary-glow);
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid var(--color-primary-glow);
}

/* ===== Header ===== */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 12px;
}

.dashboard-title {
  font-size: 24px;
  font-weight: 800;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
}
.dark .dashboard-title {
  color: #f8fafc;
}

.title-icon {
  font-size: 28px;
}

.dashboard-subtitle {
  font-size: 13px;
  color: #64748b;
  margin: 4px 0 0 0;
}
.dark .dashboard-subtitle {
  color: #94a3b8;
}

.header-date {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #475569;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 8px 14px;
}
.dark .header-date {
  color: #cbd5e1;
  background: #131926;
  border-color: var(--color-primary-glow);
}

.date-icon {
  font-size: 14px;
}

/* ===== KPI Cards Grid (Removed in favor of unified Tailwind CSS styling) ===== */

/* ===== Chart Rows ===== */
.chart-row {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 16px;
}

.chart-col-large,
.chart-col-small,
.chart-col-medium {
  min-width: 0;
}

@media (max-width: 900px) {
  .chart-row {
    grid-template-columns: 1fr !important;
  }
}

/* ===== Chart Tabs Filter ===== */
.chart-tabs {
  display: flex;
  gap: 8px;
  margin: 10px 0 16px 0;
  background: #ffffff;
  padding: 6px;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  width: fit-content;
  flex-wrap: wrap;
}
.dark .chart-tabs {
  background: #131926;
  border-color: var(--color-primary-glow);
}

.chart-tabs button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
  background: transparent;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.25s ease;
}
.dark .chart-tabs button {
  color: #94a3b8;
}

.chart-tabs button:hover {
  color: #0f172a;
  background: rgba(0, 0, 0, 0.03);
}
.dark .chart-tabs button:hover {
  color: #f8fafc;
  background: rgba(255, 255, 255, 0.05);
}

.chart-tabs button.active {
  color: #ffffff;
  background: var(--color-primary);
  box-shadow: 0 4px 12px var(--color-primary-glow);
}

.tab-icon {
  stroke-width: 2px;
  flex-shrink: 0;
}

.mb-5 {
  margin-bottom: 20px;
}

/* ===== Animate Count ===== */
.animate-count {
  animation: countUp 0.6s ease-out;
}

@keyframes countUp {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

<!-- Global chart card styles (used by child chart components) -->
<style>
.chart-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.05),
    0 2px 4px -1px rgba(0, 0, 0, 0.03);
  transition: all 0.3s ease;
}

.chart-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}

.dark .chart-card {
  background: #131926;
  border-color: var(--color-primary-glow);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.dark .chart-card:hover {
  box-shadow: 0 8px 30px var(--color-primary-glow);
  border-color: var(--color-primary);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 20px 4px 20px;
}

.chart-title {
  font-size: 15px;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}
.dark .chart-title {
  color: #f8fafc;
}

.chart-subtitle {
  font-size: 11px;
  color: #64748b;
  margin: 2px 0 0 0;
}
.dark .chart-subtitle {
  color: #94a3b8;
}

.chart-badge {
  font-size: 12px;
  font-weight: 700;
  color: var(--color-primary);
  background: var(--color-primary-glow);
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid var(--color-primary-glow);
}

.chart-body {
  padding: 8px 12px 12px 12px;
}

.chart-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 60px 20px;
  color: #64748b;
  font-size: 13px;
}
.dark .chart-loading {
  color: #94a3b8;
}

.chart-loading .spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e2e8f0;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.chart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 20px;
  color: #64748b;
  font-size: 13px;
}
.dark .chart-empty {
  color: #94a3b8;
}

.chart-empty .empty-icon {
  font-size: 36px;
  margin-bottom: 8px;
  opacity: 0.5;
}

.chart-empty p {
  margin: 0;
}

.btn-download-csv {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #475569;
  background: transparent;
  border: 1px solid #cbd5e1;
  padding: 5px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.dark .btn-download-csv {
  color: #cbd5e1;
  border: 1px solid rgba(255, 122, 0, 0.2);
}

.btn-download-csv:hover {
  color: #0f172a;
  border-color: #94a3b8;
  background: rgba(0, 0, 0, 0.02);
}
.dark .btn-download-csv:hover {
  color: #ffffff;
  border-color: var(--color-primary);
  background: var(--color-primary-glow);
  box-shadow: 0 0 8px var(--color-primary-glow);
}

.btn-download-csv svg {
  flex-shrink: 0;
}
</style>
