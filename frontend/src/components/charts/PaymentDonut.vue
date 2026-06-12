<template>
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Metode Pembayaran</h3>
        <p class="chart-subtitle">{{ subtitleLabel }}</p>
      </div>
      <div class="chart-actions">
        <!-- Filter rentang -->
        <div class="filter-tabs">
          <button
            v-for="opt in filterOptions"
            :key="opt.value"
            class="filter-tab"
            :class="{ active: activeFilter === opt.value }"
            @click="setFilter(opt.value)"
          >
            {{ opt.label }}
          </button>
        </div>
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

    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <span>Memuat data...</span>
    </div>
    <div v-else-if="hasData" class="chart-body">
      <apexchart
        type="donut"
        height="280"
        :options="chartOptions"
        :series="series"
        :key="chartKey"
      />
      <!-- Summary Cards - dengan animasi masuk -->
      <transition-group name="payment-item" tag="div" class="payment-summary">
        <div
          v-for="(item, idx) in data"
          :key="item.metode_pembayaran"
          class="payment-item"
          :style="{ animationDelay: `${idx * 60}ms` }"
        >
          <span
            class="payment-dot"
            :style="{ background: getColor(item.metode_pembayaran) }"
          ></span>
          <div class="payment-info">
            <div class="payment-left">
              <span class="payment-label">{{ formatLabel(item.metode_pembayaran) }}</span>
              <span class="payment-count">{{ item.jumlah_transaksi }} transaksi</span>
            </div>
            <div class="payment-right">
              <span class="payment-value">{{ formatCurrency(item.total_nominal) }}</span>
              <span class="payment-pct">{{ getPct(item.jumlah_transaksi) }}%</span>
            </div>
          </div>
        </div>
      </transition-group>
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
            d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"
          />
        </svg>
      </div>
      <p class="text-xs text-slate-500 font-mono">
        Belum ada transaksi {{ activeFilter === 'bulan' ? 'bulan' : 'minggu' }} ini
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ChartMetodePembayaran } from '@/types'
import { laporanService } from '@/services'
import { useTheme } from '@/utils/theme'

const { isDark } = useTheme()
const apexchart = VueApexCharts

const loading = ref(true)
const data = ref<ChartMetodePembayaran[]>([])
const activeFilter = ref<'minggu' | 'bulan' | '3bulan'>('bulan')
const chartKey = ref(0) // force re-render donut saat data berubah

const filterOptions = [
  { label: '7H', value: 'minggu' },
  { label: '1B', value: 'bulan' },
  { label: '3B', value: '3bulan' },
] as const

const subtitleLabel = computed(() => {
  const map = { minggu: '7 hari terakhir', bulan: 'Bulan ini', '3bulan': '3 bulan terakhir' }
  return map[activeFilter.value]
})

const hasData = computed(() => data.value.length > 0)
const totalTransaksi = computed(() => data.value.reduce((s, d) => s + d.jumlah_transaksi, 0))

function getPct(jumlah: number) {
  if (totalTransaksi.value === 0) return '0'
  return ((jumlah / totalTransaksi.value) * 100).toFixed(1)
}

const colorMap = computed<Record<string, string>>(() => {
  if (isDark.value) {
    return {
      tunai: '#3b82f6', // Accent (Blue) in dark mode
      transfer: '#FF7A00', // Primary (Orange) in dark mode
      debit: '#60a5fa', // Secondary Blue
      kredit: '#FFC857', // Yellow
      qris: '#00D1FF', // Cyan
    }
  } else {
    return {
      tunai: '#FF7A00', // Accent (Orange) in light mode
      transfer: '#1D4ED8', // Primary (Blue) in light mode
      debit: '#3b82f6', // Secondary Blue
      kredit: '#FFC857', // Yellow
      qris: '#60a5fa', // Lighter Blue
    }
  }
})

function getColor(method: string) {
  return colorMap.value[method.toLowerCase()] ?? '#64748b'
}

function formatLabel(method: string) {
  const labels: Record<string, string> = {
    tunai: 'Tunai',
    transfer: 'Transfer Bank',
    debit: 'Kartu Debit',
    kredit: 'Kartu Kredit',
    qris: 'QRIS',
  }
  return labels[method.toLowerCase()] ?? method
}

const series = computed(() => data.value.map((d) => d.jumlah_transaksi))

const chartOptions = computed(() => ({
  chart: {
    type: 'donut' as const,
    fontFamily: 'inherit',
    background: 'transparent',
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 600,
      dynamicAnimation: { enabled: true, speed: 400 },
    },
  },
  labels: data.value.map((d) => formatLabel(d.metode_pembayaran)),
  colors: data.value.map((d) => getColor(d.metode_pembayaran)),
  plotOptions: {
    pie: {
      donut: {
        size: '70%',
        labels: {
          show: true,
          name: {
            show: true,
            fontSize: '12px',
            fontWeight: 600,
            color: '#94a3b8',
            offsetY: -6,
          },
          value: {
            show: true,
            fontSize: '20px',
            fontWeight: 700,
            color: '#f8fafc',
            offsetY: 4,
            formatter: (val: string) => val,
          },
          total: {
            show: true,
            label: 'Total',
            fontSize: '11px',
            fontWeight: 500,
            color: '#475569',
            formatter: () => `${totalTransaksi.value} trx`,
          },
        },
      },
    },
  },
  stroke: { width: 3, colors: ['#0b0f19'] },
  legend: { show: false },
  dataLabels: {
    enabled: true,
    style: { fontSize: '11px', fontWeight: 700 },
    dropShadow: { enabled: false },
  },
  tooltip: {
    theme: 'dark' as const,
    y: {
      formatter: (val: number, opts: any) => {
        const item = data.value[opts?.seriesIndex]
        const nominal = item
          ? ' • ' +
            new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              minimumFractionDigits: 0,
            }).format(item.total_nominal)
          : ''
        return `${val} transaksi${nominal}`
      },
    },
  },
  states: {
    hover: { filter: { type: 'lighten' as any, value: 0.15 } },
    active: {
      allowMultipleDataPointsSelection: false,
      filter: { type: 'darken' as any, value: 0.15 },
    },
  },
}))

async function loadData() {
  loading.value = true
  try {
    const res = await (laporanService.getChartMetodePembayaran as any)({
      periode: activeFilter.value,
    }).catch(() => laporanService.getChartMetodePembayaran())
    data.value = res.data as ChartMetodePembayaran[]
    chartKey.value++ // force donut re-render
  } catch (e) {
    console.error('Failed to load payment method chart:', e)
    data.value = []
  } finally {
    loading.value = false
  }
}

function setFilter(val: 'minggu' | 'bulan' | '3bulan') {
  activeFilter.value = val
}

// Re-load saat filter berubah
watch(activeFilter, loadData)
watch(isDark, () => {
  chartKey.value++
})

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}

async function exportExcel() {
  const token = localStorage.getItem('auth_token') ?? ''
  const url = new URL('/api/laporan/export-excel', window.location.origin)
  url.searchParams.set('type', 'metode-pembayaran')
  url.searchParams.set('periode', activeFilter.value)
  if (token) url.searchParams.set('token', token)
  try {
    const res = await fetch(url.toString())
    const disposition = res.headers.get('Content-Disposition') ?? ''
    const match = disposition.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? `Laporan_Metode_Pembayaran.xlsx`
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

onMounted(loadData)
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
  transition:
    background 0.15s,
    color 0.15s;
  letter-spacing: 0.03em;
}

.filter-tab.active {
  background: #1e293b;
  color: #f28500;
}

.filter-tab:hover:not(.active) {
  color: #94a3b8;
}

.payment-summary {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 0 4px;
  margin-top: 8px;
}

.payment-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #0b0f19;
  border: 1px solid #1e293b;
  transition:
    border-color 0.2s,
    transform 0.15s;
  animation: slideInUp 0.3s ease both;
}

.payment-item:hover {
  border-color: #334155;
  transform: translateX(2px);
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.payment-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.payment-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex: 1;
  gap: 8px;
}

.payment-left {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.payment-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 1px;
}

.payment-label {
  font-size: 12px;
  font-weight: 600;
  color: #cbd5e1;
}

.payment-count {
  font-size: 10px;
  color: #475569;
}

.payment-value {
  font-size: 12px;
  font-weight: 700;
  color: #f8fafc;
  font-variant-numeric: tabular-nums;
}

.payment-pct {
  font-size: 10px;
  color: #475569;
  font-variant-numeric: tabular-nums;
}

/* transition-group animation */
.payment-item-enter-active {
  transition: all 0.3s ease;
}
.payment-item-leave-active {
  transition: all 0.2s ease;
}
.payment-item-enter-from {
  opacity: 0;
  transform: translateX(-8px);
}
.payment-item-leave-to {
  opacity: 0;
  transform: translateX(8px);
}
</style>
