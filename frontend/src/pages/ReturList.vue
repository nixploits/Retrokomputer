<template>
  <div class="space-y-4 font-mono">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-xl font-bold text-retro-blue"></span>
        <h2 class="text-base font-bold text-slate-800 uppercase tracking-wider">RETUR BARANG</h2>
      </div>
      <router-link
        to="/retur/tambah"
        class="text-xs font-bold px-3 py-2 bg-retro-blue text-white rounded hover:bg-blue-700 transition-colors shadow-sm"
      >
        + TAMBAH RETUR
      </router-link>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-lg border-2 border-slate-200 overflow-hidden shadow-sm">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 font-mono">
        Memuat data retur...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-200 bg-slate-50 font-bold text-xs text-slate-700">
              <th class="px-4 py-3">JENIS</th>
              <th class="px-4 py-3">REF ID</th>
              <th class="px-4 py-3 text-right">ONGKIR</th>
              <th class="px-4 py-3">ALASAN</th>
              <th class="px-4 py-3">OPERATOR</th>
              <th class="px-4 py-3">TANGGAL</th>
              <th class="px-4 py-3 text-center">AKSI</th>
            </tr>
          </thead>
          <tbody class="text-xs text-slate-600">
            <tr
              v-for="r in list"
              :key="r.id"
              class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors"
            >
              <td class="px-4 py-3">
                <span
                  class="px-2 py-0.5 rounded font-bold uppercase text-[10px]"
                  :class="
                    r.jenis_retur === 'penjualan'
                      ? 'bg-amber-100 text-amber-800 border border-amber-300'
                      : 'bg-blue-100 text-blue-800 border border-blue-300'
                  "
                >
                  {{ r.jenis_retur }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <span
                    v-if="r.jenis_retur === 'pembelian' && r.pembelian"
                    class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-[10px] font-mono font-bold text-slate-600 whitespace-nowrap"
                    >#{{ r.referensi_id }}</span
                  >
                  <span
                    class="text-xs text-slate-600 font-sans font-normal"
                    v-if="r.jenis_retur === 'pembelian' && r.pembelian"
                  >
                    Supplier:
                    <span class="font-semibold text-slate-800">{{ r.pembelian.supplier }}</span>
                  </span>
                  <span
                    class="font-bold text-slate-800"
                    v-if="!(r.jenis_retur === 'pembelian' && r.pembelian)"
                    >#{{ r.referensi_id }}</span
                  >
                </div>
              </td>
              <td class="px-4 py-3 text-right font-bold text-slate-800">
                Rp {{ formatRupiah(r.ongkir) }}
              </td>
              <td class="px-4 py-3 max-w-xs truncate text-slate-700" :title="r.alasan">
                {{ r.alasan }}
              </td>
              <td class="px-4 py-3 text-slate-500">
                {{ r.user?.name || '-' }}
              </td>
              <td class="px-4 py-3 text-slate-500">
                {{ formatDate(r.created_at) }}
              </td>
              <td class="px-4 py-3 text-center">
                <button
                  @click="showDetail(r)"
                  class="px-2.5 py-1 text-[10px] font-bold border-2 border-retro-blue text-retro-blue rounded hover:bg-retro-blue hover:text-white transition-colors"
                >
                  Detail
                </button>
              </td>
            </tr>
            <tr v-if="list.length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400 font-mono">
                Belum ada data retur barang.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <div
      v-if="activeRetur"
      class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div
        class="bg-white rounded-lg border-2 border-retro-blue overflow-hidden shadow-2xl max-w-xl w-full"
      >
        <!-- Title Bar -->
        <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">DETAIL RETUR #{{ activeRetur.id }}</span>
          <button
            @click="activeRetur = null"
            class="text-white hover:text-red-300 font-bold text-sm"
          >
            ✕
          </button>
        </div>

        <!-- Detail Content -->
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4 text-xs">
            <div class="space-y-1">
              <span class="block font-bold text-slate-500 uppercase">Jenis Retur</span>
              <span class="font-bold text-slate-800 uppercase">{{ activeRetur.jenis_retur }}</span>
            </div>
            <div class="space-y-1">
              <span class="block font-bold text-slate-500 uppercase">Referensi ID</span>
              <span class="font-bold text-slate-800 font-mono"
                >#{{ activeRetur.referensi_id }}</span
              >
            </div>
            <div
              v-if="activeRetur.jenis_retur === 'pembelian' && activeRetur.pembelian"
              class="col-span-2 space-y-1 bg-blue-50/50 p-2.5 border border-blue-100 rounded"
            >
              <span class="block font-bold text-retro-blue uppercase text-[10px] tracking-wider"
                >Supplier</span
              >
              <span class="font-bold text-slate-800 text-xs">{{
                activeRetur.pembelian.supplier
              }}</span>
            </div>
            <div class="space-y-1">
              <span class="block font-bold text-slate-500 uppercase">Ongkos Kirim</span>
              <span class="font-bold text-slate-800"
                >Rp {{ formatRupiah(activeRetur.ongkir) }}</span
              >
            </div>
            <div class="space-y-1">
              <span class="block font-bold text-slate-500 uppercase">Dibuat Oleh</span>
              <span class="font-bold text-slate-800">{{ activeRetur.user?.name || '-' }}</span>
            </div>
            <div class="col-span-2 space-y-1">
              <span class="block font-bold text-slate-500 uppercase">Alasan Retur</span>
              <p
                class="text-slate-700 bg-slate-50 p-2.5 border border-slate-200 rounded text-xs leading-relaxed"
              >
                {{ activeRetur.alasan }}
              </p>
            </div>
          </div>

          <!-- Returned Products Table -->
          <div class="border-2 border-slate-200 rounded overflow-hidden">
            <div
              class="bg-slate-50 px-3 py-2 border-b border-slate-200 text-xs font-bold text-slate-700"
            >
              DAFTAR BARANG YANG DIRETUR
            </div>
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="bg-slate-100/50 border-b border-slate-200 font-bold text-slate-600">
                  <th class="px-3 py-2 w-12 text-center">NO</th>
                  <th class="px-3 py-2">KODE</th>
                  <th class="px-3 py-2">NAMA BARANG</th>
                  <th class="px-3 py-2 text-center w-20">QTY</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in activeRetur.details"
                  :key="index"
                  class="border-b border-slate-100 last:border-0 hover:bg-slate-50/50"
                >
                  <td class="px-3 py-2 text-center text-slate-400 font-mono">{{ index + 1 }}</td>
                  <td class="px-3 py-2 font-mono text-slate-800">
                    {{ item.produk?.kode_produk || '-' }}
                  </td>
                  <td class="px-3 py-2 text-slate-700 font-bold">
                    {{ item.produk?.nama_produk || 'Produk Tidak Dikenal' }}
                  </td>
                  <td class="px-3 py-2 text-center font-bold text-retro-blue font-mono">
                    {{ item.qty }} pcs
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
          <button
            @click="activeRetur = null"
            class="px-4 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded transition-colors"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Retur } from '@/types'
import { returService } from '@/services'

const loading = ref(true)
const list = ref<Retur[]>([])
const activeRetur = ref<Retur | null>(null)

onMounted(async () => {
  try {
    const res = await returService.getAll()
    list.value = res.data as Retur[]
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
})

function formatRupiah(val: number | string): string {
  if (val === undefined || val === null) return '0'
  return new Intl.NumberFormat('id-ID').format(Number(val))
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

function showDetail(r: Retur) {
  activeRetur.value = r
}
</script>
