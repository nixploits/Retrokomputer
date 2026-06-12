<template>
  <div class="space-y-4 font-mono">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 border-2 border-retro-blue rounded-lg shadow-sm"
    >
      <div>
        <h2 class="text-sm font-bold text-slate-800">INVENTARIS BARANG</h2>
        <p class="text-[10px] text-slate-400 font-sans mt-0.5">
          Kelola seluruh produk, stok, dan kategori barang.
        </p>
      </div>
      <router-link
        to="/produk/tambah"
        class="text-xs font-bold px-4 py-2 bg-retro-blue hover:bg-blue-700 text-white rounded transition-colors shadow-sm self-start sm:self-auto"
      >
        + TAMBAH PRODUK
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border-2 border-retro-blue p-3 flex flex-col md:flex-row gap-3">
      <div class="flex-1">
        <input
          v-model="tempSearch"
          class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans"
          placeholder="Cari berdasarkan nama atau kode produk..."
        />
      </div>
      <div class="w-full md:w-64">
        <select
          v-model="selectedCategory"
          class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in uniqueCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-lg border-2 border-retro-blue overflow-hidden shadow-sm">
      <div v-if="loading" class="p-8 text-center text-sm text-slate-400 font-mono">Memuat...</div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="border-b-2 border-slate-200 bg-slate-50">
              <th class="text-xs font-bold text-slate-700 px-4 py-3 uppercase">Kode</th>
              <th class="text-xs font-bold text-slate-700 px-4 py-3 uppercase">Nama</th>
              <th class="text-xs font-bold text-slate-700 px-4 py-3 uppercase">Kategori</th>
              <th class="text-right text-xs font-bold text-slate-700 px-4 py-3 uppercase">
                Harga Jual
              </th>
              <th class="text-center text-xs font-bold text-slate-700 px-4 py-3 uppercase">Stok</th>
              <th class="text-center text-xs font-bold text-slate-700 px-4 py-3 uppercase">Aksi</th>
            </tr>
          </thead>
          <tbody class="font-sans">
            <tr
              v-for="p in filteredProduk"
              :key="p.id"
              class="border-b border-slate-100 hover:bg-slate-50 transition-colors"
            >
              <td class="px-4 py-3 text-xs font-mono font-bold text-retro-blue">
                {{ p.kode_produk }}
              </td>
              <td class="px-4 py-3 text-sm text-slate-800 font-semibold">{{ p.nama_produk }}</td>
              <td class="px-4 py-3">
                <span
                  class="text-[10px] px-2.5 py-0.5 rounded font-bold uppercase font-mono bg-retro-orange/10 text-retro-orange-dark border border-retro-orange/20"
                >
                  {{ p.kategori }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-slate-800 text-right font-mono">
                {{ formatCurrency(p.harga_jual) }}
              </td>
              <td class="px-4 py-3 text-center">
                <span
                  class="text-xs font-mono font-bold px-2 py-0.5 rounded border"
                  :class="
                    p.stok <= 0
                      ? 'bg-red-50 text-red-600 border-red-200'
                      : p.stok <= p.stok_minimum
                        ? 'bg-amber-50 text-amber-600 border-amber-200'
                        : 'bg-emerald-50 text-emerald-600 border-emerald-200'
                  "
                >
                  {{ p.stok }}
                </span>
              </td>
              <td class="px-4 py-3 text-center font-mono">
                <div class="flex items-center justify-center gap-2">
                  <router-link
                    :to="`/produk/${p.id}/edit`"
                    class="text-xs font-bold px-2.5 py-1 rounded border-2 border-slate-200 text-slate-600 hover:bg-slate-100 transition-colors"
                  >
                    EDIT
                  </router-link>
                  <button
                    @click="confirmDelete(p)"
                    class="text-xs font-bold px-2.5 py-1 rounded border-2 border-red-200 text-red-500 hover:bg-red-50 transition-colors"
                  >
                    HAPUS
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProduk.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-400 font-mono">
                TIDAK ADA PRODUK YANG COCOK
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Modal -->
    <div
      v-if="deleteTarget"
      class="fixed inset-0 bg-retro-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click.self="deleteTarget = null"
    >
      <div
        class="bg-white border-2 border-red-600 rounded-lg max-w-sm w-full overflow-hidden shadow-2xl animate-slideUp"
      >
        <!-- Title bar -->
        <div class="bg-red-600 text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">HAPUS BARANG</span>
          <button
            @click="deleteTarget = null"
            class="text-white hover:text-retro-yellow transition-colors font-bold text-lg leading-none"
          >
            ×
          </button>
        </div>
        <div class="p-6 font-sans">
          <div class="flex items-start gap-3">
            <div
              class="w-10 h-10 rounded bg-red-50 border border-red-200 flex items-center justify-center shrink-0 text-red-500 text-xl font-bold"
            >
              !
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">Konfirmasi Hapus</h3>
              <p class="text-xs text-slate-500 leading-relaxed">
                Apakah Anda yakin ingin menghapus produk
                <strong class="text-slate-800">{{ deleteTarget.nama_produk }}</strong
                >? Tindakan ini tidak dapat dibatalkan.
              </p>
            </div>
          </div>
        </div>
        <div class="bg-slate-50 px-4 py-3 flex justify-end gap-2 border-t border-slate-100">
          <button
            @click="deleteTarget = null"
            class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded font-sans transition-colors"
          >
            Batal
          </button>
          <button
            @click="doDelete"
            class="px-3 py-1.5 text-xs bg-red-600 hover:bg-red-700 text-white rounded font-sans font-semibold transition-colors shadow-sm"
          >
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { debounce } from '@/utils/debounce'
import type { Produk } from '@/types'
import { produkService } from '@/services'
import { customDialog } from '@/utils/dialog'

const tempSearch = ref('')
const search = ref('')

const updateSearch = debounce((val: string) => {
  search.value = val
}, 300)

watch(tempSearch, (newVal) => {
  updateSearch(newVal)
})
const selectedCategory = ref('')
const loading = ref(true)
const produkList = ref<Produk[]>([])
const deleteTarget = ref<Produk | null>(null)

const uniqueCategories = computed(() => {
  const cats = produkList.value.map((p) => (p.kategori || '').trim()).filter((c) => c.length > 0)
  return Array.from(new Set(cats)).sort()
})

const filteredProduk = computed(() => {
  let list = produkList.value
  if (selectedCategory.value) {
    list = list.filter((p) => (p.kategori || '').trim() === selectedCategory.value)
  }
  if (search.value) {
    const q = search.value.toLowerCase()
    list = list.filter(
      (p) => p.nama_produk.toLowerCase().includes(q) || p.kode_produk.toLowerCase().includes(q),
    )
  }
  return list
})

onMounted(async () => {
  try {
    const res = await produkService.getAll()
    produkList.value = res.data as Produk[]
  } catch {
    /* silent */
  } finally {
    loading.value = false
  }
})

function confirmDelete(p: Produk) {
  deleteTarget.value = p
}

async function doDelete() {
  if (!deleteTarget.value) return
  try {
    await produkService.delete(deleteTarget.value.id)
    produkList.value = produkList.value.filter((p) => p.id !== deleteTarget.value!.id)
  } catch {
    customDialog.error('Gagal menghapus produk')
  }
  deleteTarget.value = null
}

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}
</script>
