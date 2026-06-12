<template>
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Diagram Laba Kotor</h3>
        <p class="chart-subtitle">Breakdown pendapatan → biaya → laba bersih</p>
      </div>
    </div>

    <!-- Summary Cards -->
    <div v-if="hasData && !loading" class="waterfall-summary">
      <div class="wf-metric" style="--mc: #10b981">
        <span class="wf-metric-label">Penjualan</span>
        <span class="wf-metric-val">{{ formatCurrencyShort(penjualan) }}</span>
      </div>
      <div class="wf-metric" style="--mc: #f97316">
        <span class="wf-metric-label">HPP</span>
        <span class="wf-metric-val">-{{ formatCurrencyShort(pembelian) }}</span>
      </div>
      <div class="wf-metric" style="--mc: #ef4444">
        <span class="wf-metric-label">Kerugian</span>
        <span class="wf-metric-val">-{{ formatCurrencyShort(kerugian) }}</span>
      </div>
      <div
        class="wf-metric"
        :style="{ '--mc': labaBersih >= 0 ? 'var(--color-primary)' : '#ef4444' }"
      >
        <span class="wf-metric-label">Laba Bersih</span>
        <span class="wf-metric-val">{{ formatCurrencyShort(labaBersih) }}</span>
      </div>
    </div>

    <!-- Profit Margin Indicator -->
    <div v-if="hasData && !loading" class="margin-indicator">
      <div class="margin-bar-wrapper">
        <div class="margin-bar-track">
          <div
            class="margin-bar-fill"
            :style="{
              width: Math.min(Math.max(marginPercent, 0), 100) + '%',
              background:
                marginPercent >= 20
                  ? 'linear-gradient(90deg, #10b981, #34d399)'
                  : marginPercent >= 10
                    ? 'linear-gradient(90deg, #f59e0b, #fbbf24)'
                    : 'linear-gradient(90deg, #ef4444, #f87171)',
            }"
          ></div>
        </div>
        <span class="margin-value" :class="marginClass">{{ marginPercent.toFixed(1) }}%</span>
      </div>
      <span class="margin-label">Profit Margin</span>
    </div>

    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <span>Memuat data...</span>
    </div>
    <div v-else-if="hasData" class="chart-body">
      <apexchart
        ref="chartRef"
        type="bar"
        height="320"
        :options="chartOptions"
        :series="series"
        :key="chartKey"
      />
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
            d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"
          />
        </svg>
      </div>
      <p class="text-xs text-slate-500 font-mono">Belum ada data laba kotor</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { DashboardStats } from '@/types'
import { laporanService } from '@/services'
import { useTheme } from '@/utils/theme'

const { isDark } = useTheme()
const chartKey = ref(0)
const apexchart = VueApexCharts
const chartRef = ref<any>(null)

const loading = ref(true)
const stats = ref<DashboardStats>({
  penjualan_bulan_ini: 0,
  pembelian_bulan_ini: 0,
  laba_bersih: 0,
  total_transaksi: 0,
  kerugian_inventaris: 0,
})

const penjualan = computed(() => stats.value.penjualan_bulan_ini)
const pembelian = computed(() => stats.value.pembelian_bulan_ini)
const kerugian = computed(() => stats.value.kerugian_inventaris)
const labaBersih = computed(() => stats.value.laba_bersih)

const hasData = computed(() => penjualan.value > 0 || pembelian.value > 0)

const marginPercent = computed(() => {
  if (penjualan.value <= 0) return 0
  return (labaBersih.value / penjualan.value) * 100
})

const marginClass = computed(() => {
  if (marginPercent.value >= 20) return 'margin-good'
  if (marginPercent.value >= 10) return 'margin-warn'
  return 'margin-bad'
})

/**
 * Build waterfall series using rangeBar.
 * Each category has [start, end] values to create the waterfall effect:
 * - Penjualan: [0, penjualan] (goes up from 0)
 * - HPP:       [penjualan - pembelian, penjualan] (goes down from penjualan)
 * - Kerugian:  [penjualan - pembelian - kerugian, penjualan - pembelian] (goes down further)
 * - Laba Bersih: [0, labaBersih] (final result)
 */
const series = computed(() => {
  const p = penjualan.value
  const h = pembelian.value
  const k = kerugian.value
  const l = labaBersih.value

  const afterHpp = p - h
  const afterKerugian = afterHpp - k

  return [
    {
      name: 'Laba Kotor',
      data: [
        {
          x: 'Penjualan',
          y: [0, p],
          fillColor: '#10b981',
        },
        {
          x: 'HPP',
          y: [afterHpp, p],
          fillColor: '#f97316',
        },
        {
          x: 'Kerugian\nInventaris',
          y: [afterKerugian, afterHpp],
          fillColor: '#ef4444',
        },
        {
          x: 'Laba Bersih',
          y: [0, l],
          fillColor: l >= 0 ? (isDark.value ? '#FF7A00' : '#1D4ED8') : '#ef4444',
        },
      ],
    },
  ]
})

const chartOptions = computed(() => ({
  chart: {
    type: 'rangeBar' as const,
    height: 320,
    toolbar: { show: false },
    fontFamily: 'inherit',
    background: 'transparent',
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 800,
      animateGradually: { enabled: true, delay: 120 },
      dynamicAnimation: { enabled: true, speed: 500 },
    },
  },
  plotOptions: {
    bar: {
      horizontal: false,
      borderRadius: 6,
      borderRadiusApplication: 'end' as const,
      columnWidth: '55%',
      distributed: false,
      dataLabels: { position: 'top' },
    },
  },
  colors: ['#10b981'],
  legend: { show: false },
  dataLabels: {
    enabled: true,
    formatter: (_val: any, opts: any) => {
      const d = opts.w.config.series[0].data[opts.dataPointIndex]
      const diff = d.y[1] - d.y[0]
      // Show negative sign for deductions
      if (opts.dataPointIndex === 1 || opts.dataPointIndex === 2) {
        return '-' + formatCurrencyCompact(Math.abs(diff))
      }
      return formatCurrencyCompact(diff)
    },
    offsetY: -24,
    style: {
      fontSize: '11px',
      fontWeight: 700,
      colors: ['#94a3b8'],
    },
  },
  xaxis: {
    labels: {
      style: {
        colors: '#64748b',
        fontSize: '11px',
        fontWeight: 600,
      },
    },
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
    min: (min: number) => Math.min(min, 0),
  },
  grid: {
    borderColor: '#1e293b',
    strokeDashArray: 4,
    padding: { left: 8, right: 8, top: 8 },
    yaxis: { lines: { show: true } },
    xaxis: { lines: { show: false } },
  },
  tooltip: {
    theme: 'dark' as const,
    custom: ({ dataPointIndex, w }: any) => {
      const d = w.config.series[0].data[dataPointIndex]
      const diff = d.y[1] - d.y[0]
      const label = d.x.replace('\n', ' ')
      const isDeduction = dataPointIndex === 1 || dataPointIndex === 2
      const sign = isDeduction ? '-' : ''
      const color = isDeduction
        ? '#ef4444'
        : dataPointIndex === 3
          ? isDark.value
            ? '#FF7A00'
            : '#1D4ED8'
          : '#10b981'

      return `<div style="padding: 10px 14px; font-size: 12px; line-height: 1.6;">
        <div style="font-weight: 700; margin-bottom: 4px; color: #e2e8f0;">${label}</div>
        <div style="color: ${color}; font-weight: 700; font-size: 14px;">
          ${sign}${formatCurrencyFull(Math.abs(diff))}
        </div>
      </div>`
    },
  },
  states: {
    hover: { filter: { type: 'lighten' as any, value: 0.08 } },
    active: { filter: { type: 'darken' as any, value: 0.05 } },
  },
}))

function formatCurrencyShort(v: number) {
  const abs = Math.abs(v)
  if (abs >= 1_000_000_000) return `Rp ${(v / 1_000_000_000).toFixed(1)}M`
  if (abs >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}jt`
  if (abs >= 1_000) return `Rp ${(v / 1_000).toFixed(0)}rb`
  return `Rp ${v}`
}

function formatCurrencyCompact(v: number) {
  if (v >= 1_000_000) return `${(v / 1_000_000).toFixed(1)}jt`
  if (v >= 1_000) return `${(v / 1_000).toFixed(0)}rb`
  return v.toString()
}

function formatCurrencyFull(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}

watch(isDark, () => {
  chartKey.value++
})

onMounted(async () => {
  try {
    const res = await laporanService.getDashboard()
    stats.value = res.data
  } catch (e) {
    console.error('Failed to load gross profit data:', e)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.waterfall-summary {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 16px;
}

.wf-metric {
  display: flex;
  flex-direction: column;
  gap: 3px;
  padding: 10px 12px;
  background: #0b0f19;
  border: 1.5px solid #1e293b;
  border-left: 3px solid var(--mc);
  border-radius: 8px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.wf-metric:hover {
  background: #111827;
  border-color: var(--mc);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px color-mix(in srgb, var(--mc) 15%, transparent);
}

.wf-metric-label {
  font-size: 10px;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: color 0.2s;
}

.wf-metric:hover .wf-metric-label {
  color: #fff;
  opacity: 0.8;
}

.wf-metric-val {
  font-size: 13px;
  font-weight: 700;
  color: var(--mc);
  font-variant-numeric: tabular-nums;
}

/* Margin indicator */
.margin-indicator {
  padding: 12px 14px;
  background: #0b0f19;
  border: 1.5px solid #1e293b;
  border-radius: 8px;
  margin-bottom: 16px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.margin-indicator:hover {
  border-color: #334155;
  background: #111827;
}

.margin-bar-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.margin-bar-track {
  flex: 1;
  height: 8px;
  background: #1e293b;
  border-radius: 4px;
  overflow: hidden;
}

.margin-bar-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.margin-value {
  font-size: 14px;
  font-weight: 800;
  font-variant-numeric: tabular-nums;
  min-width: 52px;
  text-align: right;
}

.margin-good {
  color: #10b981;
}
.margin-warn {
  color: #f59e0b;
}
.margin-bad {
  color: #ef4444;
}

.margin-label {
  font-size: 10px;
  color: #475569;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

@media (max-width: 640px) {
  .waterfall-summary {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }
  .wf-metric {
    padding: 8px 10px;
  }
  .wf-metric-label {
    font-size: 9px;
  }
  .wf-metric-val {
    font-size: 12px;
  }
}
</style>
