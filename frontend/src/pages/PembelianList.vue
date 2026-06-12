<template>
  <div class="space-y-4 font-mono">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 border-2 border-retro-blue rounded-lg shadow-sm"
    >
      <div>
        <h2 class="text-sm font-bold text-slate-800">PEMBELIAN INVENTARIS</h2>
        <p class="text-[10px] text-slate-400 font-sans mt-0.5">
          Kelola riwayat pembelian barang dari supplier pihak ketiga.
        </p>
      </div>
      <router-link
        to="/pembelian/tambah"
        class="text-xs font-bold px-4 py-2 bg-retro-blue hover:bg-blue-700 text-white rounded transition-colors shadow-sm self-start sm:self-auto"
      >
        + TAMBAH PEMBELIAN
      </router-link>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg border-2 border-retro-blue overflow-hidden shadow-sm">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 font-mono">Memuat...</div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-200 bg-slate-50">
              <th class="text-xs font-bold text-slate-700 px-4 py-3 uppercase">Invoice</th>
              <th class="text-xs font-bold text-slate-700 px-4 py-3 uppercase">Supplier</th>
              <th class="text-xs font-bold text-slate-700 px-4 py-3 uppercase">Tanggal</th>
              <th class="text-right text-xs font-bold text-slate-700 px-4 py-3 uppercase">Total</th>
              <th class="text-center text-xs font-bold text-slate-700 px-4 py-3 uppercase">
                Struk
              </th>
              <th class="text-center text-xs font-bold text-slate-700 px-4 py-3 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="font-sans">
            <tr
              v-for="p in list"
              :key="p.id"
              class="border-b border-slate-100 hover:bg-slate-50 transition-colors"
            >
              <td class="px-4 py-3 text-xs font-mono font-bold text-retro-blue">{{ p.invoice }}</td>
              <td class="px-4 py-3 text-sm text-slate-800 font-semibold">{{ p.supplier }}</td>
              <td class="px-4 py-3 text-xs text-slate-500 font-mono">
                {{ formatDate(p.created_at) }}
              </td>
              <td class="px-4 py-3 text-xs text-slate-800 text-right font-mono font-bold">
                {{ formatCurrency(p.total) }}
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  v-if="p.struk"
                  @click="openStruk(p.struk)"
                  class="text-[10px] font-mono font-bold px-2 py-0.5 bg-retro-orange/10 text-retro-orange-dark border border-retro-orange/20 rounded hover:bg-retro-orange/20 transition-colors"
                >
                  Lihat Bukti
                </button>
                <span v-else class="text-slate-300 text-xs font-mono">-</span>
              </td>
              <td class="px-4 py-3 text-center font-mono">
                <button
                  @click="viewDetail(p.id)"
                  class="text-xs font-bold px-2.5 py-1 rounded border-2 border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors"
                >
                  DETAIL
                </button>
              </td>
            </tr>
            <tr v-if="list.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400 font-mono">
                BELUM ADA RIWAYAT PEMBELIAN
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Purchase Detail Modal -->
    <div
      v-if="detailItem"
      class="fixed inset-0 bg-retro-dark/60 backdrop-blur-sm flex items-center justify-center p-4"
      style="z-index: 1000"
      @click="detailItem = null"
    >
      <div
        class="bg-white border-2 border-retro-blue rounded-lg max-w-xl w-full overflow-hidden shadow-2xl animate-slideUp font-mono"
        @click.stop
      >
        <!-- Title bar -->
        <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">DETAIL NOTA PEMBELIAN</span>
          <button
            @click="detailItem = null"
            class="text-white hover:text-retro-yellow font-bold text-lg leading-none"
          >
            ×
          </button>
        </div>

        <div class="p-6 font-sans space-y-4">
          <!-- Info Grid -->
          <div
            class="grid grid-cols-2 gap-4 text-xs bg-slate-50 p-3 rounded border border-slate-200 font-mono"
          >
            <div>
              <p class="text-slate-400">NO. INVOICE:</p>
              <p class="font-bold text-slate-800 text-sm mt-0.5">{{ detailItem.invoice }}</p>
            </div>
            <div>
              <p class="text-slate-400">SUPPLIER:</p>
              <p class="font-bold text-slate-800 text-sm mt-0.5">{{ detailItem.supplier }}</p>
            </div>
            <div>
              <p class="text-slate-400">TANGGAL:</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ formatDate(detailItem.created_at) }}</p>
            </div>
            <div>
              <p class="text-slate-400">PETUGAS:</p>
              <p class="font-bold text-slate-800 mt-0.5">{{ detailItem.user?.name || 'Admin' }}</p>
            </div>
          </div>

          <!-- Items Table -->
          <div>
            <p class="text-xs font-bold text-slate-700 uppercase font-mono mb-2">
              ITEM YANG DIBELI
            </p>
            <div class="border-2 border-slate-200 rounded overflow-hidden">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr
                    class="bg-slate-100 border-b border-slate-200 font-mono font-bold text-slate-600"
                  >
                    <th class="px-3 py-2">Nama Barang</th>
                    <th class="px-3 py-2 text-center">Qty</th>
                    <th class="px-3 py-2 text-right">Harga Beli</th>
                    <th class="px-3 py-2 text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody class="font-mono">
                  <tr
                    v-for="item in detailItem.details"
                    :key="item.id"
                    class="border-b border-slate-100"
                  >
                    <td class="px-3 py-2 text-slate-800 font-sans font-semibold">
                      {{ item.produk?.nama_produk || 'Barang Dihapus' }}
                    </td>
                    <td class="px-3 py-2 text-center font-bold">{{ item.qty }}</td>
                    <td class="px-3 py-2 text-right text-slate-600">
                      {{ formatCurrency(item.harga_beli) }}
                    </td>
                    <td class="px-3 py-2 text-right font-bold text-slate-800">
                      {{ formatCurrency(item.subtotal) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Grand Total -->
          <div
            class="flex justify-between items-center bg-slate-50 p-3 rounded border-2 border-retro-blue font-mono"
          >
            <span class="text-xs font-bold text-slate-600">GRAND TOTAL PEMBELIAN:</span>
            <span class="text-base font-bold text-retro-orange-dark">{{
              formatCurrency(detailItem.total)
            }}</span>
          </div>
        </div>

        <div class="bg-slate-50 px-4 py-3 flex justify-between border-t border-slate-100 font-sans">
          <div>
            <button
              v-if="detailItem.struk"
              @click="openStruk(detailItem.struk)"
              class="text-xs font-mono font-bold px-3 py-1.5 bg-retro-orange text-white rounded hover:bg-retro-orange-dark transition-colors shadow-sm"
            >
              Lihat Struk
            </button>
          </div>
          <button
            @click="detailItem = null"
            class="text-xs font-mono font-bold px-4 py-1.5 border-2 border-slate-300 rounded hover:bg-slate-100 transition-colors"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Receipt Lightbox Modal -->
    <div
      v-if="activeStruk"
      class="fixed inset-0 bg-retro-dark/80 backdrop-blur-sm flex items-center justify-center p-4"
      style="z-index: 2000"
      @click="activeStruk = null"
    >
      <div
        class="bg-white border-2 border-retro-blue rounded-lg max-w-lg w-full overflow-hidden shadow-2xl animate-slideUp font-mono"
        @click.stop
      >
        <!-- Title bar -->
        <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">■ BUKTI NOTA / STRUK</span>
          <button
            @click="activeStruk = null"
            class="text-white hover:text-retro-yellow font-bold text-lg leading-none"
          >
            ×
          </button>
        </div>
        <div
          class="p-4 flex items-center justify-center bg-slate-900 border-b border-slate-200 min-h-[300px]"
        >
          <img
            :src="getStrukUrl(activeStruk)"
            class="max-h-[70vh] object-contain rounded border-2 border-retro-orange shadow-lg"
            alt="Bukti Struk"
          />
        </div>
        <div class="bg-slate-50 px-4 py-2.5 flex justify-end">
          <button
            @click="activeStruk = null"
            class="text-xs font-bold px-4 py-1.5 border-2 border-slate-300 rounded hover:bg-slate-100 transition-colors"
          >
            TUTUP
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Pembelian } from '@/types'
import { pembelianService } from '@/services'
import { customDialog } from '@/utils/dialog'

const loading = ref(true)
const list = ref<Pembelian[]>([])
const activeStruk = ref<string | null>(null)
const detailItem = ref<any | null>(null)

onMounted(async () => {
  try {
    const res = await pembelianService.getAll()
    list.value = res.data as Pembelian[]
  } catch (err) {
    console.error('Gagal memuat pembelian', err)
  } finally {
    loading.value = false
  }
})

function openStruk(url: string) {
  activeStruk.value = url
}

async function viewDetail(id: number) {
  try {
    const res = await pembelianService.getById(id)
    detailItem.value = res.data
  } catch (err) {
    customDialog.error('Gagal mengambil detail pembelian')
  }
}

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}

function getStrukUrl(url: string | null) {
  if (!url) return ''
  if (url.startsWith('http://localhost:8000/')) {
    return url.replace('http://localhost:8000/', '/')
  }
  if (url.startsWith('http://localhost/')) {
    return url.replace('http://localhost/', '/')
  }
  return url
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
