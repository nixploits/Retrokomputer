<template>
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Tren Penjualan Harian</h3>
        <p class="chart-subtitle">{{ subtitleLabel }}</p>
      </div>
      <div class="chart-actions">
        <!-- Badge total -->
        <div v-if="totalPenjualan > 0" class="chart-badge">
          {{ formatCurrencyShort(totalPenjualan) }}
        </div>
        <!-- Filter rentang hari -->
        <div class="filter-tabs">
          <button
            v-for="opt in rangeOptions"
            :key="opt.value"
            class="filter-tab"
            :class="{ active: selectedRange === opt.value }"
            @click="setRange(opt.value)"
          >{{ opt.label }}</button>
        </div>
        <button v-if="hasData" class="btn-download-csv" @click="exportExcel" title="Unduh Laporan Excel">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
          </svg>
          Unduh Excel
        </button>
      </div>
    </div>

    <!-- Perbandingan dengan periode sebelumnya -->
    <div v-if="hasData && !loading" class="comparison-row">
      <div class="comp-item">
        <span class="comp-label">Hari Terbaik</span>
        <span class="comp-val">{{ bestDay?.tanggal_label ?? '-' }}</span>
        <span class="comp-sub">{{ bestDay ? formatCurrencyShort(bestDay.total) : '' }}</span>
      </div>
      <div class="comp-item">
        <span class="comp-label">Rata-rata/hari</span>
        <span class="comp-val">{{ formatCurrencyShort(avgHarian) }}</span>
        <span class="comp-sub">{{ totalTransaksi }} transaksi total</span>
      </div>
      <div class="comp-item">
        <span class="comp-label">Hari Aktif</span>
        <span class="comp-val">{{ hariAktif }} hari</span>
        <span class="comp-sub">dari {{ data.length }} hari</span>
      </div>
    </div>

    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <span>Memuat data...</span>
    </div>
    <div v-else-if="hasData" class="chart-body">
      <apexchart
        type="area"
        height="260"
        :options="chartOptions"
        :series="series"
        :key="chartKey"
      />
    </div>
    <div v-else class="chart-empty">
      <div class="empty-icon-wrapper">
        <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
      </div>
      <p class="text-xs text-slate-500 font-mono">Belum ada data penjualan {{ selectedRange }} hari terakhir</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ChartPenjualanHarian } from '@/types'
import { laporanService } from '@/services'
import { useTheme } from '@/utils/theme'

const { isDark } = useTheme()
const apexchart = VueApexCharts

const isMobile = ref(false)
const checkMobile = () => {
  isMobile.value = window.innerWidth < 640
}

const loading = ref(true)
const data = ref<ChartPenjualanHarian[]>([])
const selectedRange = ref<7 | 14 | 30>(30)
const chartKey = ref(0)

const rangeOptions = [
  { label: '7H', value: 7 as const },
  { label: '14H', value: 14 as const },
  { label: '30H', value: 30 as const },
]

const subtitleLabel = computed(() => `${selectedRange.value} hari terakhir`)

const hasData = computed(() => data.value.some(d => d.total > 0))
const totalPenjualan = computed(() => data.value.reduce((s, d) => s + d.total, 0))
const totalTransaksi = computed(() => data.value.reduce((s, d) => s + (d.jumlah_transaksi ?? 0), 0))
const hariAktif = computed(() => data.value.filter(d => d.total > 0).length)
const avgHarian = computed(() => {
  return hariAktif.value > 0 ? totalPenjualan.value / hariAktif.value : 0
})

// Hari dengan penjualan tertinggi
const bestDay = computed(() => {
  if (data.value.length === 0) return null
  const sorted = [...data.value].sort((a, b) => b.total - a.total)
  const best = sorted[0]
  if (!best || best.total === 0) return null
  const date = new Date(best.tanggal)
  return {
    ...best,
    tanggal_label: date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }),
  }
})

const series = computed(() => [{
  name: 'Penjualan',
  data: data.value.map(d => d.total)
}])

const chartOptions = computed(() => ({
  chart: {
    type: 'area' as const,
    height: 260,
    toolbar: { show: false },
    fontFamily: 'inherit',
    background: 'transparent',
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 600,
      animateGradually: { enabled: true, delay: 40 },
      dynamicAnimation: { enabled: true, speed: 400 },
    },
    zoom: { enabled: false },
  },
  colors: [isDark.value ? '#FF7A00' : '#1D4ED8'],
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.02,
      stops: [0, 85, 100],
    },
  },
  stroke: {
    curve: 'smooth' as const,
    width: 2.5,
  },
  xaxis: {
    categories: data.value.map(d => {
      const date = new Date(d.tanggal)
      return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
    }),
    labels: {
      style: { colors: '#64748b', fontSize: isMobile.value ? '9px' : '10px' },
      rotate: isMobile.value ? 0 : (selectedRange.value === 30 ? -40 : -20),
      rotateAlways: false,
      hideOverlappingLabels: true,
    },
    tickAmount: isMobile.value ? 5 : (selectedRange.value === 30 ? 12 : undefined),
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: '#64748b', fontSize: '11px' },
      formatter: (val: number) => {
        if (val >= 1_000_000) return `${(val / 1_000_000).toFixed(1)}jt`
        if (val >= 1_000) return `${(val / 1_000).toFixed(0)}rb`
        return val.toString()
      },
    },
  },
  grid: {
    borderColor: '#1e293b',
    strokeDashArray: 4,
    padding: { left: 8, right: 8 },
  },
  // Annotations: garis rata-rata
  annotations: {
    yaxis: avgHarian.value > 0 ? [{
      y: avgHarian.value,
      borderColor: '#F28500',
      borderWidth: 1,
      strokeDashArray: 4,
      label: {
        text: `Avg: ${formatCurrencyShort(avgHarian.value)}`,
        style: {
          background: '#F28500',
          color: '#000',
          fontSize: '10px',
          fontWeight: 700,
          padding: { left: 6, right: 6, top: 2, bottom: 2 },
        },
        position: 'right',
        offsetX: -8,
      },
    }] : [],
  },
  tooltip: {
    theme: 'dark' as const,
    y: {
      formatter: (val: number) =>
        new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val),
    },
    // FIX: gunakan index dari data array, bukan _val string
    x: {
      formatter: (_val: any, opts?: any) => {
        const item = data.value[opts?.dataPointIndex]
        if (!item) return ''
        const date = new Date(item.tanggal)
        const tgl = date.toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long' })
        const trx = item.jumlah_transaksi != null ? ` • ${item.jumlah_transaksi} transaksi` : ''
        return `${tgl}${trx}`
      },
    },
  },
  dataLabels: { enabled: false },
  markers: {
    size: 0,
    hover: { size: 5, sizeOffset: 2 },
  },
}))

function formatCurrencyShort(v: number) {
  if (v >= 1_000_000_000) return `Rp ${(v / 1_000_000_000).toFixed(1)}M`
  if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}jt`
  if (v >= 1_000) return `Rp ${(v / 1_000).toFixed(0)}rb`
  return `Rp ${v}`
}

async function loadData() {
  loading.value = true
  try {
    const res = await (laporanService.getChartPenjualanHarian as any)({ hari: selectedRange.value })
      .catch(() => laporanService.getChartPenjualanHarian())
    data.value = res.data as ChartPenjualanHarian[]
    chartKey.value++
  } catch (e) {
    console.error('Failed to load daily sales chart:', e)
    data.value = []
  } finally {
    loading.value = false
  }
}

function setRange(val: 7 | 14 | 30) {
  selectedRange.value = val
}

watch(selectedRange, loadData)
watch(isDark, () => {
  chartKey.value++
})

async function exportExcel() {
  const token = localStorage.getItem('auth_token') ?? ''
  const url = new URL('/api/laporan/export-excel', window.location.origin)
  url.searchParams.set('type', 'penjualan-harian')
  url.searchParams.set('hari', String(selectedRange.value))
  if (token) url.searchParams.set('token', token)
  try {
    const res = await fetch(url.toString())
    const disposition = res.headers.get('Content-Disposition') ?? ''
    const match = disposition.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? `Laporan_Penjualan_Harian.xlsx`
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

onMounted(() => {
  loadData()
  checkMobile()
  window.addEventListener('resize', checkMobile)
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
  flex-wrap: wrap;
}

.filter-tabs {
  display: flex;
  gap: 2px;
  background: #0b0f19;
  border: 1px solid #1e293b;
  border-radius: 8px;
  padding: 3px;
}

.filter-tab {
  padding: 3px 10px;
  font-size: 11px;
  font-weight: 700;
  border-radius: 5px;
  border: none;
  background: transparent;
  color: #475569;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  letter-spacing: 0.03em;
}

.filter-tab.active {
  background: #1e293b;
  color: #ff7a00;
}

.filter-tab:hover:not(.active) {
  color: #94a3b8;
}

.comparison-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-bottom: 16px;
}

.comp-item {
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

.comp-item:hover {
  background: #111827;
  border-color: #ff7a00;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 122, 0, 0.15);
}

.comp-label {
  font-size: 10px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: color 0.2s;
}

.comp-item:hover .comp-label {
  color: #fdba74;
}

.comp-val {
  font-size: 13px;
  font-weight: 700;
  color: #f8fafc;
  font-variant-numeric: tabular-nums;
  transition: color 0.2s;
}

.comp-item:hover .comp-val {
  color: #fff;
}

.comp-sub {
  font-size: 10px;
  color: #475569;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}

@media (max-width: 640px) {
  .comparison-row {
    gap: 8px;
  }
  .comp-item {
    padding: 8px 10px;
  }
  .comp-label {
    font-size: 9px;
  }
  .comp-val {
    font-size: 12px;
  }
  .comp-sub {
    font-size: 9px;
  }
}
</style>
