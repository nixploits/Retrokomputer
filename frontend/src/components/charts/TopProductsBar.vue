<template>
  <div class="chart-card">
    <div class="chart-header">
      <div>
        <h3 class="chart-title">Produk Terlaris</h3>
        <p class="chart-subtitle">{{ subtitleLabel }}</p>
      </div>
      <div class="chart-actions">
        <!-- Filter periode -->
        <div class="filter-tabs">
          <button
            v-for="opt in periodeOptions"
            :key="opt.value"
            class="filter-tab"
            :class="{ active: periode === opt.value }"
            @click="setPeriode(opt.value)"
          >{{ opt.label }}</button>
        </div>
        <!-- Filter jumlah produk -->
        <div class="filter-tabs">
          <button
            v-for="opt in topOptions"
            :key="opt.value"
            class="filter-tab"
            :class="{ active: topN === opt.value }"
            @click="setTopN(opt.value)"
          >{{ opt.label }}</button>
        </div>
        <!-- Toggle tampilan: chart vs tabel -->
        <button class="view-toggle" @click="viewMode = viewMode === 'chart' ? 'table' : 'chart'" :title="viewMode === 'chart' ? 'Lihat Tabel' : 'Lihat Chart'">
          <svg v-if="viewMode === 'chart'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M3 6h18M3 18h18"/>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2"/>
          </svg>
        </button>
        <button v-if="hasData" class="btn-download-csv" @click="exportExcel" title="Unduh Laporan Excel">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
          </svg>
          Unduh Excel
        </button>
      </div>
    </div>

    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <span>Memuat data...</span>
    </div>

    <template v-else-if="hasData">
      <!-- CHART VIEW -->
      <div v-if="viewMode === 'chart'" class="chart-body">
        <apexchart
          type="bar"
          :height="chartHeight"
          :options="chartOptions"
          :series="series"
          :key="`chart-${topN}`"
        />
      </div>

      <!-- TABLE VIEW -->
      <div v-else class="table-view">
        <transition-group name="table-row" tag="div" class="table-list">
          <div
            v-for="(item, idx) in displayData"
            :key="item.nama_produk"
            class="table-row"
            :style="{ animationDelay: `${idx * 40}ms` }"
          >
            <span class="rank-badge" :class="{ gold: idx === 0, silver: idx === 1, bronze: idx === 2 }">
              {{ idx + 1 }}
            </span>
            <div class="row-info">
              <span class="row-name">{{ item.nama_produk }}</span>
              <div class="row-bar-wrap">
                <div
                  class="row-bar"
                  :style="{
                    width: `${(item.total_terjual / maxTerjual) * 100}%`,
                    background: barColors[idx % barColors.length],
                  }"
                ></div>
              </div>
            </div>
            <div class="row-stats">
              <span class="row-unit">{{ item.total_terjual }} pcs</span>
              <span class="row-revenue">{{ formatCurrencyShort(item.total_pendapatan) }}</span>
            </div>
          </div>
        </transition-group>
      </div>

      <!-- Detailed text comparison list below the chart/table -->
      <div class="products-comparison-list">
        <div class="comparison-header">Perbandingan dengan {{ periodePembandingLabel }}</div>
        <div class="comparison-items">
          <div v-for="(item, idx) in displayData" :key="item.nama_produk" class="comparison-item">
            <div class="item-info">
              <span class="item-rank">#{{ idx + 1 }}</span>
              <span class="item-name" :title="item.nama_produk">{{ item.nama_produk }}</span>
            </div>
            <div class="item-stats">
              <span class="item-qty">
                {{ item.total_terjual }} pcs <span class="qty-label">{{ periodeLabel }}</span>
              </span>
              
              <!-- Growth Indicator -->
              <span 
                v-if="item.total_terjual_bulan_lalu > 0" 
                class="growth-badge" 
                :class="getGrowthClass(item.total_terjual, item.total_terjual_bulan_lalu)"
              >
                {{ getGrowthSign(item.total_terjual, item.total_terjual_bulan_lalu) }}
                {{ getGrowthPercent(item.total_terjual, item.total_terjual_bulan_lalu) }}%
              </span>
              <span v-else class="growth-badge badge-new">Baru</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="chart-empty">
      <div class="empty-icon-wrapper">
        <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
      </div>
      <p class="text-xs text-slate-500 font-mono">Belum ada data produk terlaris</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import type { ChartProdukTerlaris } from '@/types'
import { laporanService } from '@/services'

const apexchart = VueApexCharts

const loading = ref(true)
const allData = ref<ChartProdukTerlaris[]>([])
const topN = ref<5 | 10>(5)
const viewMode = ref<'chart' | 'table'>('chart')
const periode = ref<'bulan-ini' | 'bulan-lalu' | 'tahun-ini'>('bulan-ini')

const topOptions = [
  { label: 'Top 5', value: 5 as const },
  { label: 'Top 10', value: 10 as const },
]

const periodeOptions = [
  { label: 'Bln Ini', value: 'bulan-ini' as const },
  { label: 'Bln Lalu', value: 'bulan-lalu' as const },
  { label: 'Thn Ini', value: 'tahun-ini' as const },
]

// Label periode untuk subtitle & teks pembanding
const periodeLabel = computed(() => {
  const map = { 'bulan-ini': 'bulan ini', 'bulan-lalu': 'bulan lalu', 'tahun-ini': 'tahun ini' }
  return map[periode.value]
})
const periodePembandingLabel = computed(() => {
  const map = {
    'bulan-ini': 'Bulan Lalu',
    'bulan-lalu': 'Bulan Sebelumnya',
    'tahun-ini': 'Tahun Lalu',
  }
  return map[periode.value]
})

const barColors = ['#F28500', '#FFD700', '#fb923c', '#fbbf24', '#fde68a', '#f59e0b', '#fcd34d', '#fef08a', '#fed7aa', '#fdba74']

const subtitleLabel = computed(() => `Top ${topN.value} ${periodeLabel.value}`)

// Ambil hanya top N dari data
const displayData = computed(() => allData.value.slice(0, topN.value))

const hasData = computed(() => displayData.value.length > 0)
const maxTerjual = computed(() => Math.max(...displayData.value.map(d => d.total_terjual), 1))

// Tinggi chart menyesuaikan jumlah item
const chartHeight = computed(() => Math.max(240, topN.value * 44))

const series = computed(() => [{
  name: 'Terjual',
  data: displayData.value.map(d => d.total_terjual)
}])

const chartOptions = computed(() => ({
  chart: {
    type: 'bar' as const,
    height: chartHeight.value,
    toolbar: { show: false },
    fontFamily: 'inherit',
    background: 'transparent',
    animations: {
      enabled: true,
      easing: 'easeinout',
      speed: 500,
      animateGradually: { enabled: true, delay: 80 },
      dynamicAnimation: { enabled: true, speed: 350 },
    },
  },
  plotOptions: {
    bar: {
      horizontal: true,
      borderRadius: 5,
      borderRadiusApplication: 'end' as const,
      barHeight: '62%',
      distributed: true,
      dataLabels: { position: 'center' },
    },
  },
  colors: barColors.slice(0, displayData.value.length),
  legend: { show: false },
  dataLabels: {
    enabled: true,
    textAnchor: 'middle' as const,
    formatter: (val: number) => `${val} pcs`,
    style: {
      fontSize: '11px',
      colors: ['#0D0D0D'],
      fontWeight: 700,
    },
    background: { enabled: false },
    dropShadow: { enabled: false },
  },
  xaxis: {
    labels: {
      style: { colors: '#64748b', fontSize: '11px' },
      formatter: (val: number) => {
        if (val >= 1000) return `${(val / 1000).toFixed(0)}rb`
        return String(val)
      },
    },
    axisBorder: { show: false },
    axisTicks: { show: false },
  },
  yaxis: {
    labels: {
      style: { colors: '#cbd5e1', fontSize: '11px', fontWeight: 600 },
    },
  },
  labels: displayData.value.map(d =>
    d.nama_produk.length > 20 ? d.nama_produk.substring(0, 18) + '…' : d.nama_produk
  ),
  grid: {
    borderColor: '#1e293b',
    strokeDashArray: 4,
    xaxis: { lines: { show: true } },
    yaxis: { lines: { show: false } },
  },
  tooltip: {
    theme: 'dark' as const,
    y: {
      title: { formatter: () => '' },
      formatter: (val: number, opts?: any) => {
        const item = displayData.value[opts?.dataPointIndex]
        if (!item) return `${val} unit`
        const revenue = new Intl.NumberFormat('id-ID', {
          style: 'currency', currency: 'IDR', minimumFractionDigits: 0,
        }).format(item.total_pendapatan)
        return `${val} unit • ${revenue}`
      },
    },
    x: {
      formatter: (_val: any, opts?: any) => {
        const item = displayData.value[opts?.dataPointIndex]
        return item?.nama_produk ?? ''
      },
    },
  },
  states: {
    hover: { filter: { type: 'lighten' as any, value: 0.15 } },
    active: { filter: { type: 'darken' as any, value: 0.1 } },
  },
}))

function formatCurrencyShort(v: number) {
  if (v >= 1_000_000_000) return `Rp ${(v / 1_000_000_000).toFixed(1)}M`
  if (v >= 1_000_000) return `Rp ${(v / 1_000_000).toFixed(1)}jt`
  if (v >= 1_000) return `Rp ${(v / 1_000).toFixed(0)}rb`
  return `Rp ${v}`
}

function setTopN(val: 5 | 10) {
  topN.value = val
}

function setPeriode(val: 'bulan-ini' | 'bulan-lalu' | 'tahun-ini') {
  periode.value = val
}

async function loadData() {
  loading.value = true
  try {
    const res = await (laporanService.getChartProdukTerlaris as any)({ limit: 10, periode: periode.value })
      .catch(() => laporanService.getChartProdukTerlaris())
    allData.value = res.data as ChartProdukTerlaris[]
  } catch (e) {
    console.error('Failed to load top products chart:', e)
    allData.value = []
  } finally {
    loading.value = false
  }
}

async function exportExcel() {
  const token = localStorage.getItem('auth_token') ?? ''
  const url = new URL('/api/laporan/export-excel', window.location.origin)
  url.searchParams.set('type', 'produk-terlaris')
  url.searchParams.set('limit', String(topN.value))
  url.searchParams.set('periode', periode.value)
  if (token) url.searchParams.set('token', token)
  try {
    const res = await fetch(url.toString())
    const disposition = res.headers.get('Content-Disposition') ?? ''
    const match = disposition.match(/filename="?([^"]+)"?/)
    const filename = match?.[1] ?? `Laporan_Produk_Terlaris.xlsx`
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

// Reload saat periode berubah
watch(periode, loadData)

// Helper functions for comparison
function getGrowthClass(current: number, previous: number) {
  if (current > previous) return 'badge-up'
  if (current < previous) return 'badge-down'
  return 'badge-equal'
}

function getGrowthSign(current: number, previous: number) {
  if (current > previous) return '↑ '
  if (current < previous) return '↓ '
  return ''
}

function getGrowthPercent(current: number, previous: number) {
  if (previous === 0) return 0
  const diff = ((current - previous) / previous) * 100
  return Math.abs(diff).toFixed(0)
}
</script>

<style scoped>
.chart-actions {
  display: flex;
  align-items: center;
  gap: 6px;
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
}

.filter-tab.active {
  background: #1e293b;
  color: #F28500;
}

.filter-tab:hover:not(.active) {
  color: #94a3b8;
}

.view-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  background: #0b0f19;
  border: 1px solid #1e293b;
  border-radius: 7px;
  color: #64748b;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}

.view-toggle:hover {
  border-color: #F28500;
  color: #F28500;
}

/* Table view */
.table-view {
  padding-top: 4px;
}

.table-list {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.table-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  background: #0b0f19;
  border: 1px solid #1e293b;
  border-radius: 8px;
  transition: border-color 0.2s, transform 0.15s;
  animation: fadeSlideIn 0.3s ease both;
}

.table-row:hover {
  border-color: #334155;
  transform: translateX(2px);
}

@keyframes fadeSlideIn {
  from { opacity: 0; transform: translateX(-6px); }
  to   { opacity: 1; transform: translateX(0); }
}

.rank-badge {
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 11px;
  font-weight: 800;
  background: #1e293b;
  color: #64748b;
  flex-shrink: 0;
}

.rank-badge.gold   { background: #F28500; color: #000; }
.rank-badge.silver { background: #94a3b8; color: #000; }
.rank-badge.bronze { background: #78350f; color: #fbbf24; }

.row-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.row-name {
  font-size: 12px;
  font-weight: 600;
  color: #cbd5e1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.row-bar-wrap {
  height: 4px;
  background: #1e293b;
  border-radius: 2px;
  overflow: hidden;
}

.row-bar {
  height: 100%;
  border-radius: 2px;
  transition: width 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.row-stats {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 1px;
  flex-shrink: 0;
}

.row-unit {
  font-size: 12px;
  font-weight: 700;
  color: #f8fafc;
  font-variant-numeric: tabular-nums;
}

.row-revenue {
  font-size: 10px;
  color: #475569;
  font-variant-numeric: tabular-nums;
}

/* transition-group */
.table-row-enter-active { transition: all 0.3s ease; }
.table-row-leave-active { transition: all 0.2s ease; }
.table-row-enter-from  { opacity: 0; transform: translateY(-4px); }
.table-row-leave-to    { opacity: 0; transform: translateY(4px); }

/* Detailed comparison list styles */
.products-comparison-list {
  border-top: 1px solid #1e293b;
  padding: 14px 12px 12px 12px;
  margin-top: 4px;
}

.comparison-header {
  font-size: 11px;
  font-weight: 700;
  color: #ff7a00;
  margin-bottom: 10px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.comparison-items {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.comparison-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #0b0f19;
  border-radius: 8px;
  border: 1px solid #1e293b;
  transition: border-color 0.2s, transform 0.15s;
}

.comparison-item:hover {
  border-color: #334155;
  transform: translateX(2px);
}

.item-info {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.item-rank {
  font-size: 10px;
  font-weight: 800;
  color: #94a3b8;
  background: #1e293b;
  width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  flex-shrink: 0;
}

.item-name {
  font-size: 12px;
  font-weight: 600;
  color: #e2e8f0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-stats {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.item-qty {
  font-size: 11px;
  font-weight: 700;
  color: #cbd5e1;
}

.qty-label {
  font-size: 9px;
  font-weight: 400;
  color: #475569;
}

.growth-badge {
  font-size: 9px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  min-width: 42px;
  justify-content: center;
}

.badge-up {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.badge-down {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.badge-equal {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
}

.badge-new {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}
</style>
