<template>
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Penjualan per Bulan</h3>
        <p class="chart-subtitle">12 bulan terakhir</p>
      </div>
      <div class="chart-actions">
        <!-- Filter tahun -->
        <select v-model="selectedYear" class="year-select" @change="filterByYear">
          <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
        </select>
        <button
          v-if="hasData"
          class="btn-download-csv"
          @click="exportExcel"
          title="Unduh Laporan Excel"
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
          Unduh Excel
        </button>
      </div>
    </div>

    <!-- Summary stats -->
    <div v-if="hasData && !loading" class="stats-row">
      <div class="stat-pill">
        <span class="stat-label">Total</span>
        <span class="stat-value">{{ formatCurrencyShort(totalPenjualan) }}</span>
      </div>
      <div class="stat-pill">
        <span class="stat-label">Tertinggi</span>
        <span class="stat-value highlight">{{ formatCurrencyShort(maxPenjualan) }}</span>
      </div>
      <div class="stat-pill">
        <span class="stat-label">Rata-rata</span>
        <span class="stat-value">{{ formatCurrencyShort(avgPenjualan) }}</span>
      </div>
    </div>

    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <span>Memuat data...</span>
    </div>
    <div v-else-if="hasData" class="chart-body">
      <apexchart type="bar" height="280" :options="chartOptions" :series="series" />
    </div>
    <div v-else class="chart-empty">
      <div class="empty-icon-wrapper">
        <svg
          class="w-8 h-8 text-slate-500 mx-auto mb-2"
          fill="none"
          stroke="currentColor"
          stroke-width="1.5"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v14.25c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125V4.875zM13.5 10.125c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v9c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-9z"
          />
        </svg>
      </div>
      <p class="text-xs text-slate-500 font-mono">Belum ada data penjualan</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ChartPenjualanBulanan } from '@/types'
import { laporanService } from '@/services'

const apexchart = VueApexCharts

const isMobile = ref(false)
const checkMobile = () => {
  isMobile.value = window.innerWidth < 640
}

const loading = ref(true)
const allData = ref<ChartPenjualanBulanan[]>([])
const selectedYear = ref<number>(new Date().getFullYear())

// Semua tahun yang tersedia dari data
const availableYears = computed(() => {
  const years = [...new Set(allData.value.map((d) => d.tahun))].sort((a, b) => b - a)
  return years.length > 0 ? years : [new Date().getFullYear()]
})

// Data difilter per tahun yang dipilih
const data = computed(() => allData.value.filter((d) => d.tahun === selectedYear.value))

const hasData = computed(() => data.value.some((d) => d.total_penjualan > 0))

const totalPenjualan = computed(() => data.value.reduce((s, d) => s + d.total_penjualan, 0))
const maxPenjualan = computed(() => Math.max(...data.value.map((d) => d.total_penjualan), 0))
const avgPenjualan = computed(() => {
  const aktif = data.value.filter((d) => d.total_penjualan > 0)
  return aktif.length > 0 ? totalPenjualan.value / aktif.length : 0
})

const series = computed(() => [
  {
    name: 'Penjualan',
    data: data.value.map((d) => d.total_penjualan),
  },
])

const currentMonth = new Date().getMonth() + 1
const currentYear = new Date().getFullYear()

const chartOptions = computed(() => {
  return {
    chart: {
      type: 'bar' as const,
      height: 280,
      toolbar: { show: false },
      fontFamily: 'inherit',
      background: 'transparent',
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 500,
        animateGradually: { enabled: true, delay: 60 },
        dynamicAnimation: { enabled: true, speed: 350 },
      },
      events: {
        dataPointMouseEnter: (_event: any, _chart: any, config: any) => {
          // highlight cursor
          const el = document.querySelector('.apexcharts-bar-area') as HTMLElement
          if (el) el.style.cursor = 'pointer'
        },
      },
    },
    plotOptions: {
      bar: {
        horizontal: false,
        borderRadius: 5,
        borderRadiusApplication: 'end' as const,
        columnWidth: '52%',
        distributed: true,
        dataLabels: { position: 'top' },
      },
    },
    // FIX: warna per bar — bulan ini highlight orange, bulan lain slate
    colors: data.value.map((d) =>
      d.bulan_num === currentMonth && d.tahun === currentYear
        ? '#F28500'
        : d.total_penjualan === maxPenjualan.value && maxPenjualan.value > 0
          ? '#FFD700'
          : '#1A3A5C',
    ),
    legend: { show: false },
    dataLabels: {
      enabled: true,
      formatter: (val: number) => {
        if (val >= 1000000) return `${(val / 1000000).toFixed(1)}jt`
        if (val >= 1000) return `${(val / 1000).toFixed(0)}rb`
        return val.toString()
      },
      offsetY: -22,
      style: { fontSize: '10px', colors: ['#94a3b8'], fontWeight: 600 },
    },
    xaxis: {
      categories: data.value.map((d) => d.bulan),
      labels: {
        style: { colors: '#64748b', fontSize: isMobile.value ? '9px' : '10px' },
        rotate: isMobile.value ? 0 : -30,
        rotateAlways: false,
        hideOverlappingLabels: true,
      },
      tickAmount: isMobile.value ? 6 : undefined,
      axisBorder: { show: false },
      axisTicks: { show: false },
    },
    yaxis: {
      labels: {
        style: { colors: '#64748b', fontSize: '11px' },
        formatter: (val: number) => {
          if (val >= 1000000) return `${(val / 1000000).toFixed(1)}jt`
          if (val >= 1000) return `${(val / 1000).toFixed(0)}rb`
          return val.toString()
        },
      },
    },
    grid: {
      borderColor: '#1e293b',
      strokeDashArray: 4,
      yaxis: { lines: { show: true } },
      xaxis: { lines: { show: false } },
    },
    tooltip: {
      theme: 'dark' as const,
      y: {
        title: { formatter: () => 'Penjualan: ' },
        formatter: (val: number) =>
          new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
          }).format(val),
      },
      states: {
        hover: { filter: { type: 'lighten' as any, value: 0.15 } },
        active: { filter: { type: 'darken' as any, value: 0.1 } },
      },
    },
  }
})

function filterByYear() {
  // reactive — computed data sudah auto-filter, ini hanya trigger jika perlu refetch
}

function formatCurrencyShort(v: number) {
  if (v >= 1_000_000_000) return `Rp ${(v / 1_000_000_000).toFixed(1)}M`
  if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}jt`
  if (v >= 1_000) return `Rp ${(v / 1_000).toFixed(0)}rb`
  return `Rp ${v}`
}

async function exportExcel() {
  const token = localStorage.getItem('auth_token') ?? ''
  const url = new URL('/api/laporan/export-excel', window.location.origin)
  url.searchParams.set('type', 'penjualan-bulanan')
  url.searchParams.set('tahun', String(selectedYear.value))
  if (token) url.searchParams.set('token', token)
  try {
    const res = await fetch(url.toString())
    const disposition = res.headers.get('Content-Disposition') ?? ''
    const match = disposition.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? `Laporan_Penjualan_Bulanan.xlsx`
    const blob = await res.blob()
    const a = document.createElement('a')
    a.href = URL.createObjectURL(blob)
    a.download = filename
    a.click()
    URL.revokeObjectURL(a.href)
  } catch (e) {
    console.error('Download failed:', e)
  }
}

onMounted(async () => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  try {
    const res = await laporanService.getChartPenjualanBulanan()
    allData.value = res.data as ChartPenjualanBulanan[]

    // Set default ke tahun paling baru
    if (availableYears.value.length > 0) {
      selectedYear.value = availableYears.value[0] as number
    }
  } catch (e) {
    console.error('Failed to load monthly sales chart:', e)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.chart-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.year-select {
  padding: 4px 10px;
  font-size: 12px;
  font-weight: 600;
  background: #0b0f19;
  border: 1px solid #1e293b;
  border-radius: 6px;
  color: #94a3b8;
  cursor: pointer;
  transition:
    border-color 0.2s,
    color 0.2s;
  outline: none;
}

.year-select:hover,
.year-select:focus {
  border-color: #f28500;
  color: #f28500;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-bottom: 16px;
}

.stat-pill {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 10px 12px;
  background: #0b0f19;
  border: 1.5px solid #1e293b;
  border-radius: 8px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.stat-pill:hover {
  background: #111827;
  border-color: #f28500;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(242, 133, 0, 0.15);
}

.stat-label {
  font-size: 10px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: color 0.2s;
}

.stat-pill:hover .stat-label {
  color: #fdba74;
}

.stat-value {
  font-size: 13px;
  font-weight: 700;
  color: #f8fafc;
  font-variant-numeric: tabular-nums;
  transition: color 0.2s;
}

.stat-pill:hover .stat-value {
  color: #fff;
}

.stat-value.highlight {
  color: #f28500;
}

@media (max-width: 640px) {
  .stats-row {
    gap: 8px;
  }
  .stat-pill {
    padding: 8px 10px;
  }
  .stat-label {
    font-size: 9px;
  }
  .stat-value {
    font-size: 12px;
  }
}
</style>
