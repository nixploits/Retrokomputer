<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- LEFT PANEL: Form for Admins, Infographic Analysis for Owner -->
    <div class="lg:col-span-1 self-start">
      <!-- OWNER INFOGRAPHIC PANEL -->
      <div
        v-if="isOwner"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
      >
        <!-- Title Bar -->
        <div
          class="px-5 py-3 flex justify-between items-center border-b border-slate-100 dark:border-slate-800"
        >
          <span class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-200"
            >Analisis Kerugian</span
          >
          <span
            class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400"
            >Loss</span
          >
        </div>

        <div class="p-5 space-y-5">
          <!-- Total Financial Loss Callout -->
          <div
            class="p-4 rounded-lg text-center bg-rose-50/50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30"
          >
            <span
              class="block text-[10px] font-semibold uppercase tracking-widest text-rose-600 dark:text-rose-400"
              >Total Kerugian Finansial</span
            >
            <div class="text-xl font-bold tracking-tight mt-1 text-slate-800 dark:text-rose-300">
              Rp {{ formatRupiah(totalFinancialLoss) }}
            </div>
            <span class="block text-[9px] mt-1 text-slate-400 dark:text-slate-500"
              >Berdasarkan Harga Beli Terakhir</span
            >
          </div>

          <!-- Breakdown metrics -->
          <div class="space-y-3 text-xs">
            <div
              class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800"
            >
              <span
                class="font-semibold uppercase tracking-wide flex items-center gap-2 text-slate-700 dark:text-slate-300"
              >
                <span class="w-2 h-2 rounded-full bg-retro-primary"></span>
                Barang Rusak
              </span>
              <div class="text-right">
                <span class="font-bold text-slate-800 dark:text-slate-100">{{ rusakQty }} pcs</span>
                <span class="block text-[10px] text-slate-400 dark:text-slate-500"
                  >Rp {{ formatRupiah(rusakLoss) }}</span
                >
              </div>
            </div>
            <div
              class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800"
            >
              <span
                class="font-semibold uppercase tracking-wide flex items-center gap-2 text-slate-700 dark:text-slate-300"
              >
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Barang Hilang
              </span>
              <div class="text-right">
                <span class="font-bold text-slate-800 dark:text-slate-100"
                  >{{ hilangQty }} pcs</span
                >
                <span class="block text-[10px] text-slate-400 dark:text-slate-500"
                  >Rp {{ formatRupiah(hilangLoss) }}</span
                >
              </div>
            </div>
          </div>

          <!-- Ratio Visual Bar -->
          <div class="space-y-2">
            <div class="flex justify-between text-[10px] font-semibold">
              <span class="text-retro-primary">Rusak ({{ ratioRusak }}%)</span>
              <span class="text-rose-550 dark:text-rose-450">Hilang ({{ ratioHilang }}%)</span>
            </div>
            <div
              class="relative w-full h-2 rounded-full overflow-hidden flex bg-slate-100 dark:bg-slate-800"
            >
              <div
                class="h-full rounded-l-full transition-all duration-700 ease-out"
                :style="{
                  width: `${ratioRusak}%`,
                  background:
                    'linear-gradient(90deg, var(--color-primary) 0%, var(--color-primary-glow) 100%)',
                }"
              ></div>
              <div
                class="h-full rounded-r-full transition-all duration-700 ease-out"
                :style="{
                  width: `${ratioHilang}%`,
                  background:
                    'linear-gradient(90deg, rgba(244, 63, 94, 0.5) 0%, rgba(251, 113, 133, 0.3) 100%)',
                }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ADMIN RECORD FORM -->
      <div
        v-else
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
      >
        <div
          class="px-5 py-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800"
        >
          <div
            class="w-1 h-5 rounded-full bg-gradient-to-b from-retro-accent to-retro-primary"
          ></div>
          <h3
            class="text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200"
          >
            Catat Kerugian Inventaris
          </h3>
        </div>

        <form @submit.prevent="submitRecord" class="p-5 space-y-4">
          <!-- Product Dropdown -->
          <div>
            <label class="block text-[11px] font-semibold mb-1.5 text-slate-500 dark:text-slate-400"
              >Pilih Produk</label
            >
            <select
              v-model="form.produk_id"
              class="w-full px-3 py-2 text-xs rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-retro-primary"
              required
            >
              <option value="" disabled>-- Pilih Produk --</option>
              <option v-for="p in products" :key="p.id" :value="p.id" :disabled="p.stok <= 0">
                {{ p.kode_produk }} - {{ p.nama_produk }} (Stok: {{ p.stok }})
              </option>
            </select>
          </div>

          <!-- Category -->
          <div>
            <label class="block text-[11px] font-semibold mb-1.5 text-slate-500 dark:text-slate-400"
              >Kategori Laporan</label
            >
            <div class="flex gap-4">
              <label
                class="inline-flex items-center text-xs cursor-pointer text-slate-700 dark:text-slate-300"
              >
                <input
                  v-model="form.kategori"
                  type="radio"
                  value="rusak"
                  class="mr-1.5 accent-retro-primary"
                />
                Barang Rusak
              </label>
              <label
                class="inline-flex items-center text-xs cursor-pointer text-slate-700 dark:text-slate-300"
              >
                <input
                  v-model="form.kategori"
                  type="radio"
                  value="hilang"
                  class="mr-1.5 accent-retro-primary"
                />
                Barang Hilang
              </label>
            </div>
          </div>

          <!-- Quantity -->
          <div>
            <label class="block text-[11px] font-semibold mb-1.5 text-slate-500 dark:text-slate-400"
              >Jumlah (Qty)</label
            >
            <input
              v-model.number="form.qty"
              type="number"
              min="1"
              class="w-full px-3 py-2 text-xs rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-retro-primary"
              placeholder="1"
              required
            />
          </div>

          <!-- Description -->
          <div>
            <label class="block text-[11px] font-semibold mb-1.5 text-slate-500 dark:text-slate-400"
              >Keterangan / Alasan</label
            >
            <textarea
              v-model="form.keterangan"
              rows="3"
              class="w-full px-3 py-2 text-xs rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-850 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-retro-primary"
              placeholder="Tulis alasan atau kronologi detail..."
            ></textarea>
          </div>

          <!-- Bukti Fisik File Upload (Damaged goods only) -->
          <div v-if="form.kategori === 'rusak'">
            <label class="block text-[11px] font-semibold mb-1.5 text-slate-500 dark:text-slate-400"
              >Bukti Fisik Kerusakan (Foto)</label
            >
            <input
              type="file"
              accept="image/*"
              @change="handleFileChange"
              class="w-full text-xs cursor-pointer text-slate-500 dark:text-slate-400"
            />
          </div>

          <button
            :disabled="submitting || products.length === 0"
            type="submit"
            class="w-full py-2.5 text-xs font-semibold rounded-lg transition-all duration-200 disabled:opacity-40 bg-retro-primary hover:bg-retro-primary-hover text-white dark:bg-retro-primary/20 dark:hover:bg-retro-primary/40 dark:text-retro-primary dark:border dark:border-retro-primary/30"
          >
            {{ submitting ? 'Memproses...' : 'Catat Kerugian' }}
          </button>
        </form>
      </div>
    </div>

    <!-- History Panel -->
    <div
      class="lg:col-span-2 flex flex-col bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm"
    >
      <div
        class="px-5 py-4 flex justify-between items-center border-b border-slate-100 dark:border-slate-800"
      >
        <h3
          class="text-xs font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-200"
        >
          Riwayat Penyesuaian Kerugian
        </h3>
        <span class="text-[10px] font-medium text-slate-400 dark:text-slate-500"
          >Real-time inventory loss logs</span
        >
      </div>

      <div class="overflow-x-auto flex-1">
        <div v-if="loading" class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
          Memuat data riwayat...
        </div>
        <table v-else class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="border-b border-slate-100 dark:border-slate-800">
              <th
                class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Tanggal
              </th>
              <th
                class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Produk
              </th>
              <th
                class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-center text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Kategori
              </th>
              <th
                class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-center text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Qty
              </th>
              <th
                class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Keterangan
              </th>
              <th
                class="py-2.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-center text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-950/40"
              >
                Bukti
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="history.length === 0">
              <td colspan="6" class="py-8 text-center text-sm text-slate-400 dark:text-slate-500">
                Belum ada catatan kerugian barang.
              </td>
            </tr>
            <tr
              v-for="item in history"
              :key="item.id"
              class="transition-colors duration-200 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 border-b border-slate-100 dark:border-slate-800/50"
            >
              <td class="py-3 px-3 font-mono whitespace-nowrap text-slate-500 dark:text-slate-400">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="py-3 px-3">
                <span class="block font-semibold text-slate-700 dark:text-slate-300">{{
                  item.produk?.nama_produk
                }}</span>
                <span class="text-[10px] font-mono text-slate-450 dark:text-slate-500">{{
                  item.produk?.kode_produk
                }}</span>
              </td>
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <span
                  v-if="item.kategori === 'rusak'"
                  class="inline-block px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide rounded-md bg-retro-primary/10 text-retro-primary border border-retro-primary/20"
                >
                  Rusak
                </span>

                <span
                  v-else
                  class="inline-block px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide rounded-md bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40"
                >
                  Hilang
                </span>
              </td>
              <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-slate-100">
                {{ item.qty }}
              </td>
              <td
                class="py-3 px-3 max-w-[200px] truncate text-slate-500 dark:text-slate-400"
                :title="item.keterangan"
              >
                {{ item.keterangan || '-' }}
              </td>
              <td class="py-3 px-3 text-center whitespace-nowrap">
                <button
                  v-if="item.bukti_foto"
                  @click="openLightbox(item.bukti_foto)"
                  class="text-[10px] font-semibold px-2.5 py-1 rounded-md transition-all duration-200 bg-retro-accent/10 text-retro-accent border border-retro-accent/20 hover:bg-retro-accent/20"
                >
                  Lihat Foto
                </button>
                <span v-else class="text-slate-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Lightbox Modal -->
    <div
      v-if="activeBukti"
      class="fixed inset-0 flex items-center justify-center p-4 z-[3000] bg-slate-900/80 dark:bg-black/80 backdrop-blur-md"
      @click="activeBukti = null"
    >
      <div
        class="max-w-lg w-full overflow-hidden animate-slideUp bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl"
        @click.stop
      >
        <div
          class="px-5 py-3.5 flex items-center justify-between border-b border-slate-100 dark:border-slate-800"
        >
          <span class="text-xs font-semibold tracking-wide text-slate-700 dark:text-slate-200"
            >Bukti Fisik Kerusakan</span
          >
          <button
            @click="activeBukti = null"
            class="w-7 h-7 rounded-lg flex items-center justify-center text-lg leading-none transition-all duration-200 text-slate-450 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800"
          >
            ×
          </button>
        </div>
        <div
          class="p-4 flex items-center justify-center min-h-[300px] bg-slate-50 dark:bg-slate-950"
        >
          <img
            :src="getCleanUrl(activeBukti)"
            class="max-h-[70vh] object-contain rounded-lg border border-slate-200 dark:border-slate-800 shadow-md"
            alt="Bukti Fisik"
          />
        </div>
        <div class="px-5 py-3 flex justify-end border-t border-slate-100 dark:border-slate-800">
          <button
            @click="activeBukti = null"
            class="text-xs font-semibold px-4 py-1.5 rounded-lg transition-all duration-200 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 dark:text-slate-300 dark:border-slate-700"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { produkService, barangRusakService } from '@/services'
import type { Produk, BarangRusak, BarangRusakPayload } from '@/types'
import { useAuthStore } from '@/stores/auth'
import { customDialog } from '@/utils/dialog'

const authStore = useAuthStore()
const products = ref<Produk[]>([])
const history = ref<BarangRusak[]>([])

const loading = ref(true)
const submitting = ref(false)

const isOwner = computed(() => authStore.isOwner)
const selectedFile = ref<File | null>(null)
const activeBukti = ref<string | null>(null)

const form = ref<BarangRusakPayload>({
  produk_id: 0,
  qty: 1,
  kategori: 'rusak',
  keterangan: '',
})

onMounted(async () => {
  await Promise.all([fetchProducts(), fetchHistory()])
})

// Infographic Calculations
const totalFinancialLoss = computed(() => {
  return history.value.reduce(
    (acc, item) => acc + Number(item.qty) * (Number(item.produk?.harga_beli) || 0),
    0,
  )
})

const rusakQty = computed(() => {
  return history.value
    .filter((item) => item.kategori === 'rusak')
    .reduce((acc, item) => acc + Number(item.qty), 0)
})

const hilangQty = computed(() => {
  return history.value
    .filter((item) => item.kategori === 'hilang')
    .reduce((acc, item) => acc + Number(item.qty), 0)
})

const rusakLoss = computed(() => {
  return history.value
    .filter((item) => item.kategori === 'rusak')
    .reduce((acc, item) => acc + Number(item.qty) * (Number(item.produk?.harga_beli) || 0), 0)
})

const hilangLoss = computed(() => {
  return history.value
    .filter((item) => item.kategori === 'hilang')
    .reduce((acc, item) => acc + Number(item.qty) * (Number(item.produk?.harga_beli) || 0), 0)
})

const totalQty = computed(() => rusakQty.value + hilangQty.value)

const ratioRusak = computed(() => {
  if (totalQty.value === 0) return 0
  return Math.round((rusakQty.value / totalQty.value) * 100)
})

const ratioHilang = computed(() => {
  if (totalQty.value === 0) return 0
  return Math.round((hilangQty.value / totalQty.value) * 100)
})

async function fetchProducts() {
  try {
    const res = await produkService.getAll()
    products.value = res.data
  } catch (err) {
    console.error('Gagal mengambil data produk:', err)
  }
}

async function fetchHistory() {
  loading.value = true
  try {
    const res = await barangRusakService.getAll()
    history.value = res.data
  } catch (err) {
    console.error('Gagal mengambil data riwayat:', err)
  } finally {
    loading.value = false
  }
}

async function submitRecord() {
  if (form.value.produk_id === 0) {
    customDialog.warning('Silakan pilih produk terlebih dahulu!')
    return
  }

  const selectedProduct = products.value.find((p) => p.id === form.value.produk_id)
  if (selectedProduct && selectedProduct.stok < form.value.qty) {
    customDialog.warning(`Stok produk tidak mencukupi! Stok saat ini: ${selectedProduct.stok}`)
    return
  }

  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('produk_id', form.value.produk_id.toString())
    formData.append('qty', form.value.qty.toString())
    formData.append('kategori', form.value.kategori)
    formData.append('keterangan', form.value.keterangan || '')
    if (form.value.kategori === 'rusak' && selectedFile.value) {
      formData.append('bukti_file', selectedFile.value)
    }

    await barangRusakService.create(formData)
    customDialog.success('Kerugian inventaris berhasil dicatat!')

    // Reset Form
    form.value = {
      produk_id: 0,
      qty: 1,
      kategori: 'rusak',
      keterangan: '',
    }
    selectedFile.value = null

    // Reset file input element if found
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement
    if (fileInput) fileInput.value = ''

    // Refresh Data
    await Promise.all([fetchProducts(), fetchHistory()])
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Gagal menyimpan catatan kerugian.'
    customDialog.error(msg)
  } finally {
    submitting.value = false
  }
}

function handleFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

function openLightbox(url: string) {
  activeBukti.value = url
}

function getCleanUrl(url: string | null) {
  if (!url) return ''
  if (url.startsWith('http://localhost:8000/')) {
    return url.replace('http://localhost:8000/', '/')
  }
  if (url.startsWith('http://localhost/')) {
    return url.replace('http://localhost/', '/')
  }
  return url
}

function formatRupiah(val: number | string): string {
  if (val === undefined || val === null) return '0'
  return new Intl.NumberFormat('id-ID').format(Number(val))
}

function formatDate(dateStr: string) {
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
