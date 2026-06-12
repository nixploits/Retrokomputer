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
        <span class="filter-bullet">■</span>
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
    <div class="kpi-grid">
      <!-- Penjualan -->
      <div class="kpi-card kpi-sales">
        <div class="kpi-icon-wrap kpi-icon-sales">
          <svg
            width="20"
            height="20"
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
        </div>
        <div class="kpi-content">
          <p class="kpi-label">{{ kpiPenjualanLabel }}</p>
          <p class="kpi-value" :class="{ 'animate-count': !loading }">
            {{ formatCurrency(stats.penjualan_bulan_ini) }}
          </p>
          <div
            v-if="percentChange.penjualan !== null && !filterMode"
            class="kpi-change"
            :class="percentChange.penjualan >= 0 ? 'change-up' : 'change-down'"
          >
            <span>{{ percentChange.penjualan >= 0 ? '↑' : '↓' }}</span>
            {{ Math.abs(percentChange.penjualan).toFixed(1) }}% vs bulan lalu
          </div>
          <div
            v-else
            class="kpi-change change-up"
            style="background: rgba(59, 130, 246, 0.1); color: #3b82f6"
          >
            {{ kpiSublabel }}
          </div>
        </div>
      </div>

      <!-- Laba Bersih -->
      <div class="kpi-card kpi-profit">
        <div
          class="kpi-icon-wrap"
          :class="stats.laba_bersih >= 0 ? 'kpi-icon-profit' : 'kpi-icon-loss'"
        >
          <svg
            v-if="stats.laba_bersih >= 0"
            width="20"
            height="20"
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
          <svg
            v-else
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <polyline points="23 18 13.5 8.5 8.5 13.5 1 6" />
            <polyline points="17 18 23 18 23 12" />
          </svg>
        </div>
        <div class="kpi-content">
          <p class="kpi-label">Laba Bersih</p>
          <p class="kpi-value" :class="stats.laba_bersih >= 0 ? 'text-emerald' : 'text-red'">
            {{ formatCurrency(stats.laba_bersih) }}
          </p>
          <div
            v-if="percentChange.laba !== null && !filterMode"
            class="kpi-change"
            :class="percentChange.laba >= 0 ? 'change-up' : 'change-down'"
          >
            <span>{{ percentChange.laba >= 0 ? '↑' : '↓' }}</span>
            {{ Math.abs(percentChange.laba).toFixed(1) }}% vs bulan lalu
          </div>
          <div
            v-else
            class="kpi-change change-up"
            style="background: rgba(59, 130, 246, 0.1); color: #3b82f6"
          >
            {{ kpiSublabel }}
          </div>
        </div>
      </div>

      <!-- Total Transaksi -->
      <div class="kpi-card kpi-transactions">
        <div class="kpi-icon-wrap kpi-icon-trx">
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
          </svg>
        </div>
        <div class="kpi-content">
          <p class="kpi-label">Total Transaksi</p>
          <p class="kpi-value">{{ stats.total_transaksi }}</p>
          <div
            v-if="percentChange.transaksi !== null && !filterMode"
            class="kpi-change"
            :class="percentChange.transaksi >= 0 ? 'change-up' : 'change-down'"
          >
            <span>{{ percentChange.transaksi >= 0 ? '↑' : '↓' }}</span>
            {{ Math.abs(percentChange.transaksi).toFixed(1) }}% vs bulan lalu
          </div>
          <div
            v-else
            class="kpi-change change-up"
            style="background: rgba(59, 130, 246, 0.1); color: #3b82f6"
          >
            {{ kpiSublabel }}
          </div>
        </div>
      </div>

      <!-- Pembelian -->
      <div class="kpi-card kpi-purchases">
        <div class="kpi-icon-wrap kpi-icon-purchase">
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <circle cx="9" cy="21" r="1" />
            <circle cx="20" cy="21" r="1" />
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
          </svg>
        </div>
        <div class="kpi-content">
          <p class="kpi-label">{{ kpiPembelianLabel }}</p>
          <p class="kpi-value">{{ formatCurrency(stats.pembelian_bulan_ini) }}</p>
          <div
            v-if="percentChange.pembelian !== null && !filterMode"
            class="kpi-change"
            :class="percentChange.pembelian <= 0 ? 'change-up' : 'change-down'"
          >
            <span>{{ percentChange.pembelian <= 0 ? '↓' : '↑' }}</span>
            {{ Math.abs(percentChange.pembelian).toFixed(1) }}% vs bulan lalu
          </div>
          <div
            v-else
            class="kpi-change change-up"
            style="background: rgba(59, 130, 246, 0.1); color: #3b82f6"
          >
            {{ kpiSublabel }}
          </div>
        </div>
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
  color: var(--color-primary);
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

/* ===== KPI Cards Grid ===== */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

@media (max-width: 1024px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }
}

.kpi-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  padding: 18px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  box-shadow:
    0 4px 6px -1px rgba(0, 0, 0, 0.05),
    0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.kpi-card:hover {
  transform: translateY(-2px);
  border-color: #cbd5e1;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
}

.dark .kpi-card {
  background: #131926;
  border-color: var(--color-primary-glow);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.dark .kpi-card:hover {
  box-shadow: 0 8px 30px var(--color-primary-glow);
  border-color: var(--color-primary);
}

.kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
}

.kpi-sales::before {
  background: linear-gradient(90deg, #ff7a00, #fed7aa);
}
.kpi-profit::before {
  background: linear-gradient(90deg, var(--color-success), var(--color-success-glow));
}
.kpi-transactions::before {
  background: linear-gradient(90deg, #1d4ed8, #60a5fa);
}
.kpi-purchases::before {
  background: linear-gradient(90deg, #f97316, #fed7aa);
}

.kpi-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  flex-shrink: 0;
}

.kpi-icon-sales {
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  color: #ff7a00;
}
.kpi-icon-profit {
  background: var(--color-success-glow);
  color: var(--color-success);
}
.kpi-icon-loss {
  background: linear-gradient(135deg, #fef2f2, #fecaca);
  color: #ef4444;
}
.kpi-icon-trx {
  background: linear-gradient(135deg, #dbeafe, #eff6ff);
  color: #1d4ed8;
}
.kpi-icon-purchase {
  background: linear-gradient(135deg, #fff7ed, #fed7aa);
  color: #f97316;
}

.kpi-content {
  flex: 1;
  min-width: 0;
}

.kpi-label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
}
.dark .kpi-label {
  color: #94a3b8;
}

.kpi-value {
  font-size: 20px;
  font-weight: 800;
  color: #0f172a;
  margin: 4px 0 0 0;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.dark .kpi-value {
  color: #f8fafc;
}

.kpi-value.text-emerald {
  color: var(--color-success);
}
.kpi-value.text-red {
  color: #dc2626;
}

.kpi-change {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  margin-top: 6px;
}

.change-up {
  background: var(--color-success-glow);
  color: var(--color-success);
}

.change-down {
  background: #fef2f2;
  color: #dc2626;
}

.kpi-sparkline {
  position: absolute;
  bottom: 0;
  right: 0;
  opacity: 0.6;
  pointer-events: none;
}

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
