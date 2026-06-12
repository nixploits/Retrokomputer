<template>
  <div class="max-w-xl mx-auto font-mono">
    <div class="bg-white rounded-lg border-2 border-retro-blue overflow-hidden shadow-md">
      <!-- Title Bar -->
      <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
        <span class="font-bold text-xs">TAMBAH RETUR BARANG</span>
      </div>

      <div class="p-6 font-sans">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Jenis Retur -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Retur</label>
            <select
              v-model="form.jenis_retur"
              @change="handleJenisReturChange"
              class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue"
              required
            >
              <option value="" disabled>-- Pilih Jenis Retur --</option>
              <option value="penjualan">Retur Penjualan (Dari Customer)</option>
              <option value="pembelian">Retur Pembelian (Ke Supplier)</option>
            </select>
          </div>

          <!-- Searchable Reference ID Selector -->
          <div v-if="form.jenis_retur" class="relative">
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Referensi
              {{ form.jenis_retur === 'penjualan' ? 'Transaksi Penjualan' : 'Invoice Pembelian' }}
            </label>

            <!-- Custom Searchable Input Dropdown -->
            <div class="flex gap-2">
              <div class="relative flex-1">
                <input
                  v-model="tempSearchQuery"
                  @focus="showDropdown = true"
                  @input="showDropdown = true"
                  :placeholder="
                    selectedRefLabel ? selectedRefLabel : 'Cari kode transaksi / invoice...'
                  "
                  class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                />

                <!-- Clear Button -->
                <button
                  v-if="form.referensi_id"
                  type="button"
                  @click="clearSelectedRef"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm"
                >
                  ✕
                </button>
              </div>
            </div>

            <!-- Dropdown Menu Options -->
            <div
              v-if="showDropdown && filteredRefs.length > 0"
              class="absolute z-10 w-full mt-1 bg-white border-2 border-slate-200 rounded shadow-lg max-h-48 overflow-y-auto font-mono text-xs"
            >
              <div
                v-for="item in filteredRefs"
                :key="item.id"
                @click="selectRef(item)"
                class="px-3 py-2 hover:bg-retro-blue hover:text-white cursor-pointer border-b border-slate-100 last:border-0"
              >
                <div class="font-bold">
                  {{ form.jenis_retur === 'penjualan' ? item.kode_transaksi : item.invoice }}
                </div>
                <div class="text-[10px] opacity-80 flex justify-between">
                  <span>{{ formatDate(item.created_at) }}</span>
                  <span>Rp {{ formatRupiahDisplay(item.total) }}</span>
                </div>
              </div>
            </div>

            <div
              v-else-if="showDropdown && searchQuery && filteredRefs.length === 0"
              class="absolute z-10 w-full mt-1 bg-white border-2 border-slate-200 rounded p-3 shadow-lg text-center text-xs text-slate-400 font-mono"
            >
              Tidak ditemukan data referensi
            </div>
          </div>

          <!-- Ongkir Field -->
          <div v-if="form.referensi_id">
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
              >Ongkos Kirim (Rp)</label
            >
            <input
              v-model="ongkirInput"
              @input="handleOngkirInput"
              placeholder="0"
              class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
            />
          </div>

          <!-- Alasan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
              >Alasan Retur</label
            >
            <textarea
              v-model="form.alasan"
              rows="2"
              placeholder="Alasan pengembalian barang..."
              class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue"
              required
            ></textarea>
          </div>

          <!-- Item Retur Selection -->
          <div v-if="form.referensi_id" class="border-2 border-dashed border-slate-200 rounded p-4">
            <div
              class="text-xs font-bold text-slate-700 uppercase mb-3 flex items-center justify-between"
            >
              <span>Item Tersedia untuk Retur</span>
              <span class="text-[10px] text-slate-400 italic">Pilih item yang ingin diretur</span>
            </div>

            <div v-if="loadingItems" class="py-4 text-center text-xs text-slate-400 font-mono">
              Memuat daftar item...
            </div>

            <div
              v-else-if="availableItems.length === 0"
              class="py-4 text-center text-xs text-slate-400 font-mono"
            >
              Tidak ada item di referensi ini.
            </div>

            <div v-else class="space-y-3 font-mono text-xs">
              <div
                v-for="item in availableItems"
                :key="item.produk_id"
                class="p-3 border border-slate-200 rounded flex items-center justify-between hover:bg-slate-50 transition-colors"
                :class="{ 'border-retro-blue bg-blue-50/20': item.selected }"
              >
                <div class="flex items-start gap-2.5">
                  <input
                    type="checkbox"
                    v-model="item.selected"
                    @change="toggleItem(item)"
                    class="mt-1 w-4 h-4 rounded text-retro-blue border-slate-300 focus:ring-retro-blue"
                  />
                  <div>
                    <div class="font-bold text-slate-800">{{ item.nama_produk }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">
                      Kode: {{ item.kode_produk }} |
                      {{ form.jenis_retur === 'penjualan' ? 'Jual' : 'Beli' }}:
                      {{ item.max_qty }} pcs
                    </div>
                  </div>
                </div>

                <!-- Quantity input if selected -->
                <div v-if="item.selected" class="flex items-center gap-1.5">
                  <span class="text-[10px] text-slate-400">Qty Retur:</span>
                  <input
                    type="number"
                    v-model.number="item.qty"
                    min="1"
                    :max="item.max_qty"
                    class="w-16 px-1.5 py-1 border-2 border-slate-200 rounded text-center font-bold text-sm focus:outline-none focus:border-retro-blue"
                    required
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Error Alert -->
          <div
            v-if="error"
            class="p-3 rounded bg-red-50 border-2 border-red-200 text-red-600 text-xs font-mono"
          >
            <strong>ERROR:</strong> {{ error }}
          </div>

          <!-- Form Buttons -->
          <div class="flex justify-end gap-2 pt-2">
            <router-link
              to="/retur"
              class="text-xs font-mono font-bold px-3 py-2 rounded border-2 border-slate-200 text-slate-600 hover:bg-slate-50"
            >
              Batal
            </router-link>
            <button
              type="submit"
              :disabled="saving || !form.referensi_id || selectedItemsCount === 0"
              class="text-xs font-mono font-bold px-4 py-2 rounded bg-retro-blue text-white hover:bg-blue-700 disabled:opacity-50 shadow-sm"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from '@/utils/debounce'
import { transaksiService, pembelianService, returService } from '@/services'
import type { ReturPayload } from '@/types'

const router = useRouter()
const saving = ref(false)
const error = ref('')
const loadingItems = ref(false)

// State lists for searchable dropdowns
const transactions = ref<any[]>([])
const purchases = ref<any[]>([])
const tempSearchQuery = ref('')
const searchQuery = ref('')
const showDropdown = ref(false)
const selectedRefLabel = ref('')

const updateSearchQuery = debounce((val: string) => {
  searchQuery.value = val
}, 300)

watch(tempSearchQuery, (newVal) => {
  updateSearchQuery(newVal)
})

// Form state - initialized fully empty as requested!
const form = ref<any>({
  jenis_retur: '',
  referensi_id: '',
  alasan: '',
  ongkir: '',
  items: [],
})

const ongkirInput = ref('')

// Track available products inside the selected transaction or purchase
const availableItems = ref<any[]>([])

const filteredRefs = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  if (form.value.jenis_retur === 'penjualan') {
    return transactions.value.filter(
      (t) => t.kode_transaksi.toLowerCase().includes(query) || t.id.toString() === query,
    )
  } else if (form.value.jenis_retur === 'pembelian') {
    return purchases.value.filter(
      (p) => p.invoice.toLowerCase().includes(query) || p.id.toString() === query,
    )
  }
  return []
})

const selectedItemsCount = computed(() => {
  return availableItems.value.filter((i) => i.selected).length
})

onMounted(async () => {
  try {
    // Preload both list sources
    const tRes = await transaksiService.getAll()
    transactions.value = tRes.data

    const pRes = await pembelianService.getAll()
    purchases.value = pRes.data
  } catch (err) {
    error.value = 'Gagal memuat data transaksi/pembelian'
  }
})

// Formatting and input handlers
function formatRupiahDisplay(val: string | number): string {
  if (!val) return '0'
  return new Intl.NumberFormat('id-ID').format(Number(val))
}

function handleOngkirInput(e: Event) {
  const target = e.target as HTMLInputElement
  const raw = target.value.replace(/\D/g, '').slice(0, 9)
  ongkirInput.value = raw ? formatRupiahDisplay(raw) : ''
  form.value.ongkir = raw ? Number(raw) : ''
}

function handleJenisReturChange() {
  // Clear selected reference and items when type changes
  clearSelectedRef()
  error.value = ''
}

function clearSelectedRef() {
  form.value.referensi_id = ''
  tempSearchQuery.value = ''
  searchQuery.value = ''
  selectedRefLabel.value = ''
  availableItems.value = []
  showDropdown.value = false
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

async function selectRef(item: any) {
  form.value.referensi_id = item.id
  tempSearchQuery.value = ''
  searchQuery.value = ''
  showDropdown.value = false
  loadingItems.value = true
  error.value = ''

  if (form.value.jenis_retur === 'penjualan') {
    selectedRefLabel.value = `${item.kode_transaksi} (Total: Rp ${formatRupiahDisplay(item.total)})`
    try {
      const res = await transaksiService.getById(item.id)
      const details = res.data.details || []
      availableItems.value = details.map((d: any) => ({
        produk_id: d.produk_id,
        nama_produk: d.produk?.nama_produk || 'Produk Tidak Dikenal',
        kode_produk: d.produk?.kode_produk || '',
        max_qty: d.qty,
        qty: d.qty,
        selected: false,
      }))
    } catch (err) {
      error.value = 'Gagal memuat detail transaksi'
    } finally {
      loadingItems.value = false
    }
  } else {
    selectedRefLabel.value = `${item.invoice} (Total: Rp ${formatRupiahDisplay(item.total)})`
    try {
      const res = await pembelianService.getById(item.id)
      const details = res.data.details || []
      availableItems.value = details.map((d: any) => ({
        produk_id: d.produk_id,
        nama_produk: d.produk?.nama_produk || 'Produk Tidak Dikenal',
        kode_produk: d.produk?.kode_produk || '',
        max_qty: d.qty,
        qty: d.qty,
        selected: false,
      }))
    } catch (err) {
      error.value = 'Gagal memuat detail pembelian'
    } finally {
      loadingItems.value = false
    }
  }
}

function toggleItem(item: any) {
  // If toggled on, verify qty is valid
  if (item.selected) {
    if (!item.qty || item.qty < 1) item.qty = 1
    if (item.qty > item.max_qty) item.qty = item.max_qty
  }
}

// Close searchable dropdown on click outside
window.addEventListener('click', (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.relative')) {
    showDropdown.value = false
  }
})

async function handleSubmit() {
  saving.value = true
  error.value = ''

  // Compile selected items
  const payloadItems = availableItems.value
    .filter((i) => i.selected)
    .map((i) => {
      // Validate bounds
      if (i.qty > i.max_qty) {
        throw new Error(
          `Kuantitas retur untuk ${i.nama_produk} melebihi jumlah pembelian (${i.max_qty}).`,
        )
      }
      return {
        produk_id: i.produk_id,
        qty: Number(i.qty),
      }
    })

  if (payloadItems.length === 0) {
    error.value = 'Harap pilih minimal satu produk untuk diretur.'
    saving.value = false
    return
  }

  const payload: ReturPayload = {
    jenis_retur: form.value.jenis_retur,
    referensi_id: Number(form.value.referensi_id),
    alasan: form.value.alasan,
    ongkir: Number(form.value.ongkir) || 0,
    items: payloadItems,
  }

  try {
    await returService.create(payload)
    router.push('/retur')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal menyimpan retur'
  } finally {
    saving.value = false
  }
}
</script>
