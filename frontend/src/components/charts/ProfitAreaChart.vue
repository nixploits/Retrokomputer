<template>
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Tren Laba Bersih</h3>
        <p class="chart-subtitle">{{ rangeMonths }} bulan terakhir</p>
      </div>
      <div class="chart-actions">
        <!-- Toggle rentang waktu -->
        <div class="filter-tabs">
          <button
            v-for="opt in rangeOptions"
            :key="opt.value"
            class="filter-tab"
            :class="{ active: rangeMonths === opt.value }"
            @click="rangeMonths = opt.value"
          >{{ opt.label }}</button>
        </div>
        <!-- Toggle series visibility -->
        <div class="legend-toggles">
          <button
            v-for="s in seriesConfig"
            :key="s.key"
            class="legend-btn"
            :class="{ dimmed: !visibleSeries[s.key] }"
            :style="{ '--series-color': s.color }"
            @click="toggleSeries(s.key)"
            :title="visibleSeries[s.key] ? `Sembunyikan ${s.label}` : `Tampilkan ${s.label}`"
          >
            <span class="legend-dot"></span>
            {{ s.label }}
          </button>
        </div>
        <button v-if="hasData" class="btn-download-csv" @click="exportExcel" title="Unduh Laporan Excel">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
          </svg>
          Unduh Excel
        </button>
      </div>
    </div>

    <!-- Ringkasan bulan terakhir -->
    <div v-if="hasData && !loading && latestData" class="latest-summary">
      <div class="latest-item">
        <span class="latest-label">Bulan Ini ({{ latestData.bulan }})</span>
      </div>
      <div class="latest-metrics">
        <div class="metric" v-for="s in seriesConfig" :key="s.key" :style="{ '--mc': s.color }">
          <span class="metric-label">{{ s.label }}</span>
          <span class="metric-val">{{ formatCurrencyShort(latestData[s.key] ?? 0) }}</span>
        </div>
      </div>
    </div>

    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <span>Memuat data...</span>
    </div>
    <div v-else-if="hasData" class="chart-body">
      <apexchart
        ref="chartRef"
        type="line"
        height="280"
        :options="chartOptions"
        :series="filteredSeries"
        :key="chartKey"
      />
    </div>
    <div v-else class="chart-empty">
      <div class="empty-icon-wrapper">
        <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
      </div>
      <p class="text-xs text-slate-500 font-mono">Belum ada data laba kotor</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, reactive, onUnmounted, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
// FIX: import type yang pasti ada, ChartLabaRugi di-extend dari ChartPenjualanBulanan
//      jika tipe ChartLabaRugi belum ada di @/types, ganti dengan baris di bawah:
//      type ChartLabaRugi = ChartPenjualanBulanan & { total_pembelian: number; laba_bersih: number }
import type { ChartPenjualanBulanan } from '@/types'

type ChartLabaRugi = ChartPenjualanBulanan & {
  total_pembelian: number
  laba_bersih: number
}
import { laporanService } from '@/services'
import { useTheme } from '@/utils/theme'

const { isDark } = useTheme()
const chartKey = ref(0)
const apexchart = VueApexCharts
const chartRef = ref<any>(null)

const isMobile = ref(false)
const checkMobile = () => {
  isMobile.value = window.innerWidth < 640
}

const loading = ref(true)
// FIX: tipe data menggunakan ChartLabaRugi, bukan ChartPenjualanBulanan
const data = ref<ChartLabaRugi[]>([])

// Rentang waktu yang ditampilkan (default 6 bulan terakhir biar tidak banyak ruang kosong)
const rangeMonths = ref<6 | 12>(6)
const rangeOptions = [
  { label: '6 Bln', value: 6 as const },
  { label: '12 Bln', value: 12 as const },
]

// Data yang ditampilkan = N bulan terakhir
const visibleData = computed(() => data.value.slice(-rangeMonths.value))

const seriesConfig = computed(() => [
  { key: 'total_penjualan' as const, label: 'Penjualan', color: isDark.value ? '#FF7A00' : '#1D4ED8', type: 'column' as const },
  { key: 'total_pembelian' as const, label: 'Pembelian', color: isDark.value ? '#3b82f6' : '#FF7A00', type: 'column' as const },
  { key: 'laba_bersih'     as const, label: 'Laba Bersih', color: '#10b981', type: 'line' as const },
])

const visibleSeries = reactive<Record<string, boolean>>({
  total_penjualan: true,
  total_pembelian: true,
  laba_bersih: true,
})

function toggleSeries(key: string) {
  visibleSeries[key] = !visibleSeries[key]
}

const hasData = computed(() =>
  data.value.some(d => d.total_penjualan > 0 || d.total_pembelian > 0)
)

// Data bulan paling akhir
const latestData = computed(() => {
  const aktif = data.value.filter(d => d.total_penjualan > 0 || d.laba_bersih !== undefined)
  return aktif.length > 0 ? aktif[aktif.length - 1] : null
})

const filteredSeries = computed(() =>
  seriesConfig.value
    .filter(s => visibleSeries[s.key])
    .map(s => ({
      name: s.label,
      type: s.type,
      data: visibleData.value.map(d => d[s.key] ?? 0),
      color: s.color,
    }))
)

const chartOptions = computed(() => {
  const visibleCfg = seriesConfig.value.filter(s => visibleSeries[s.key])
  return {
    chart: {
      type: 'line' as const,
      height: 280,
      stacked: false,
      toolbar: { show: false },
      fontFamily: 'inherit',
      background: 'transparent',
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 600,
        dynamicAnimation: { enabled: true, speed: 400 },
      },
      zoom: { enabled: false },
    },
    colors: visibleCfg.map(s => s.color),
    plotOptions: {
      bar: {
        columnWidth: '60%',
        borderRadius: 4,
        borderRadiusApplication: 'end' as const,
      },
    },
    fill: {
      // Bar solid, garis tidak diisi
      type: visibleCfg.map(s => (s.type === 'line' ? 'solid' : 'solid')),
      opacity: visibleCfg.map(s => (s.type === 'line' ? 1 : 0.9)),
    },
    stroke: {
      // Garis lurus (bukan spline) agar tidak menyesatkan; lebar 0 untuk bar
      curve: 'straight' as const,
      width: visibleCfg.map(s => (s.type === 'line' ? 3 : 0)),
    },
    markers: {
      size: visibleCfg.map(s => (s.type === 'line' ? 4 : 0)),
      hover: { size: 6 },
    },
    xaxis: {
      categories: visibleData.value.map(d => d.bulan),
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
          const abs = Math.abs(val)
          if (abs >= 1_000_000) return `${(val / 1_000_000).toFixed(1)}jt`
          if (abs >= 1_000) return `${(val / 1_000).toFixed(0)}rb`
          return val.toString()
        },
      },
    },
    grid: {
      borderColor: '#1e293b',
      strokeDashArray: 4,
      padding: { left: 8, right: 8 },
    },
    legend: { show: false }, // kita custom sendiri
    tooltip: {
      theme: 'dark' as const,
      shared: true,
      intersect: false,
      y: {
        formatter: (val: number) =>
          new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val),
      },
    },
    dataLabels: { enabled: false },
  }
})

function formatCurrencyShort(v: number) {
  const abs = Math.abs(v)
  const sign = v < 0 ? '-' : ''
  if (abs >= 1_000_000_000) return `${sign}Rp ${(abs / 1_000_000_000).toFixed(1)}M`
  if (abs >= 1_000_000) return `${sign}Rp ${(abs / 1_000_000).toFixed(1)}jt`
  if (abs >= 1_000) return `${sign}Rp ${(abs / 1_000).toFixed(0)}rb`
  return `${sign}Rp ${abs}`
}

async function exportExcel() {
  const token = localStorage.getItem('auth_token') ?? ''
  const url = new URL('/api/laporan/export-excel', window.location.origin)
  url.searchParams.set('type', 'laba-rugi-bulanan')
  if (token) url.searchParams.set('token', token)
  try {
    const res = await fetch(url.toString())
    const disposition = res.headers.get('Content-Disposition') ?? ''
    const match = disposition.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? `Laporan_Laba_Kotor_Bulanan.xlsx`
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
// FIX: getChartLabaRugiBulanan — jika belum ada di service, fallback ke getChartPenjualanBulanan
    const serviceMethod = (laporanService as any).getChartLabaRugiBulanan
      ?? laporanService.getChartPenjualanBulanan
    const res = await serviceMethod.call(laporanService)
    data.value = res.data as ChartLabaRugi[]
  } catch (e) {
    console.error('Failed to load profit area chart:', e)
  } finally {
    loading.value = false
  }
})

watch(isDark, () => {
  chartKey.value++
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

.legend-toggles {
  display: flex;
  gap: 4px;
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
  color: #F28500;
}

.filter-tab:hover:not(.active) {
  color: #94a3b8;
}

.legend-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  font-size: 11px;
  font-weight: 600;
  border-radius: 6px;
  border: 1px solid #1e293b;
  background: #0b0f19;
  color: var(--series-color);
  cursor: pointer;
  transition: opacity 0.2s, border-color 0.2s, background 0.2s;
}

.legend-btn:hover {
  border-color: var(--series-color);
  background: color-mix(in srgb, var(--series-color) 10%, #0b0f19);
}

.legend-btn.dimmed {
  opacity: 0.35;
  filter: grayscale(0.6);
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--series-color);
  flex-shrink: 0;
}

.latest-summary {
  padding: 0 0 12px 0;
}

.latest-item {
  margin-bottom: 6px;
}

.latest-label {
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.latest-metrics {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.metric {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 10px 12px;
  background: #0b0f19;
  border: 1.5px solid #1e293b;
  border-left: 3px solid var(--mc);
  border-radius: 8px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.metric:hover {
  background: #111827;
  border-color: var(--mc);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px color-mix(in srgb, var(--mc) 15%, transparent);
}

.metric-label {
  font-size: 10px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: color 0.2s;
}

.metric:hover .metric-label {
  color: #fff;
  opacity: 0.8;
}

.metric-val {
  font-size: 13px;
  font-weight: 700;
  color: var(--mc);
  font-variant-numeric: tabular-nums;
}

@media (max-width: 640px) {
  .latest-metrics {
    gap: 8px;
  }
  .metric {
    padding: 8px 10px;
  }
  .metric-label {
    font-size: 9px;
  }
  .metric-val {
    font-size: 12px;
  }
}
</style>
