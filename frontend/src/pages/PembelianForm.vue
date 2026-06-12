<template>
  <div class="max-w-3xl mx-auto font-mono">
    <div class="bg-white rounded-lg border-2 border-retro-blue shadow-md">
      <!-- Title bar -->
      <div
        class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between rounded-t-md"
      >
        <span class="font-bold text-xs">■ TAMBAH PEMBELIAN BARANG</span>
      </div>

      <div class="p-6 font-sans">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Supplier</label>
              <select
                v-model="form.supplier"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans"
                required
              >
                <option value="" disabled>-- Pilih Supplier --</option>
                <option v-for="s in supplierList" :key="s.id" :value="s.nama">{{ s.nama }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
                >No. Invoice / Referensi</label
              >
              <input
                v-model="form.invoice"
                placeholder="INV-XXXX"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                required
              />
            </div>
          </div>

          <!-- Struk File Uploader -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
              >Struk / Bukti Pembelian (Gambar)</label
            >
            <input
              type="file"
              accept="image/*"
              @change="handleFileChange"
              class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded file:border-2 file:border-retro-blue file:text-xs file:font-bold file:bg-white file:text-retro-blue hover:file:bg-slate-50 cursor-pointer"
            />
            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (maks. 2MB)</p>
          </div>

          <!-- Items Section -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-slate-700 uppercase">DETAIL BARANG</label>
              <button
                type="button"
                @click="addRow"
                class="text-xs font-mono font-bold text-retro-blue hover:underline uppercase"
              >
                + TAMBAH BARANG
              </button>
            </div>

            <!-- Items Table -->
            <div class="border-2 border-slate-200 rounded-lg bg-white">
              <!-- Clear Table Headers -->
              <div
                class="grid grid-cols-[1fr_100px_150px_45px] gap-3 px-3 py-2 bg-slate-50 border-b-2 border-slate-200 text-left font-bold text-[10px] uppercase text-slate-600 font-mono rounded-t-md"
              >
                <div>Nama Barang</div>
                <div>Jumlah (Qty)</div>
                <div>Harga Beli (Rp)</div>
                <div class="text-center">Hapus</div>
              </div>

              <!-- Rows -->
              <div class="p-3 space-y-3">
                <div
                  v-for="(item, i) in form.items"
                  :key="i"
                  class="grid grid-cols-[1fr_100px_150px_45px] gap-3 items-center"
                >
                  <div>
                    <div class="relative">
                      <input
                        v-model="item.search"
                        @focus="filterProducts(Number(i))"
                        @input="onSearchInput(Number(i))"
                        placeholder="Cari Produk..."
                        class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded focus:outline-none focus:border-retro-blue font-sans"
                        autocomplete="off"
                      />
                      <ul
                        v-if="item.filtered && item.filtered.length"
                        class="absolute z-10 w-full bg-white border border-slate-300 rounded shadow-lg max-h-48 overflow-y-auto mt-1"
                      >
                        <li
                          v-for="p in item.filtered"
                          :key="p.id"
                          @click="selectProduct(Number(i), p)"
                          class="px-2 py-1 text-xs hover:bg-slate-100 cursor-pointer"
                        >
                          {{ p.nama_produk }} (Stok: {{ p.stok }})
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div>
                    <input
                      v-model.number="item.qty"
                      type="number"
                      min="1"
                      placeholder="Qty"
                      class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded focus:outline-none focus:border-retro-blue font-mono font-bold"
                      required
                    />
                  </div>
                  <div>
                    <input
                      v-model="item.harga_beli_input"
                      @input="handleHargaBeliInput(Number(i))"
                      placeholder="Harga Satuan"
                      class="w-full px-2 py-1.5 text-xs border border-slate-300 rounded focus:outline-none focus:border-retro-blue font-mono font-bold"
                      required
                    />
                  </div>
                  <div class="text-center font-mono">
                    <button
                      v-if="form.items.length > 1"
                      type="button"
                      @click="form.items.splice(i, 1)"
                      class="text-red-500 hover:text-red-700 text-xs font-bold font-mono"
                      title="Hapus baris"
                    >
                      X
                    </button>
                    <span v-else class="text-slate-300 text-xs">-</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="error"
            class="p-3 rounded bg-red-50 border-2 border-red-200 text-red-600 text-xs font-mono"
          >
            <strong>ERROR:</strong> {{ error }}
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <router-link
              to="/pembelian"
              class="text-xs font-mono font-bold px-3 py-2 rounded border-2 border-slate-200 text-slate-600 hover:bg-slate-55"
            >
              Batal
            </router-link>
            <button
              type="submit"
              :disabled="saving"
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
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from '@/utils/debounce'
import type { Produk, PembelianPayload } from '@/types'
import { produkService, pembelianService, supplierService } from '@/services'

const router = useRouter()
const saving = ref(false)
const error = ref('')
const produkList = ref<Produk[]>([])
const supplierList = ref<any[]>([])
const selectedFile = ref<File | null>(null)

const form = ref<any>({
  supplier: '',
  invoice: '',
  items: [
    { produk_id: '', qty: '', harga_beli: '', harga_beli_input: '', search: '', filtered: [] },
  ],
})

function addRow() {
  form.value.items.push({
    produk_id: '',
    qty: '',
    harga_beli: '',
    harga_beli_input: '',
    search: '',
    filtered: [],
  })
}

function handleFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    selectedFile.value = target.files[0]
  }
}

function filterProducts(index: number) {
  const term = form.value.items[index].search?.toLowerCase() || ''
  form.value.items[index].filtered = produkList.value.filter((p: Produk) =>
    p.nama_produk.toLowerCase().includes(term),
  )
}

const debouncedFilterProducts = debounce((index: number) => {
  filterProducts(index)
}, 300)

function onSearchInput(index: number) {
  form.value.items[index].produk_id = ''
  debouncedFilterProducts(index)
}

function selectProduct(index: number, product: Produk) {
  form.value.items[index].produk_id = product.id
  form.value.items[index].search = product.nama_produk
  form.value.items[index].filtered = []
}

function formatRupiah(val: string | number): string {
  const digits = String(val).replace(/\D/g, '')
  if (!digits) return ''
  const truncated = digits.slice(0, 9)
  return new Intl.NumberFormat('id-ID').format(Number(truncated))
}

function handleHargaBeliInput(index: number) {
  const item = form.value.items[index]
  const raw = String(item.harga_beli_input).replace(/\D/g, '').slice(0, 9)
  item.harga_beli_input = formatRupiah(raw)
  item.harga_beli = raw ? Number(raw) : ''
}

const clickOutsideHandler = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.relative')) {
    form.value.items.forEach((item: any) => {
      item.filtered = []
    })
  }
}

onMounted(async () => {
  window.addEventListener('click', clickOutsideHandler)

  try {
    const pRes = await produkService.getAll()
    produkList.value = pRes.data as Produk[]
  } catch (err) {
    console.error('Gagal mengambil produk', err)
  }

  try {
    const sRes = await supplierService.getAll()
    supplierList.value = sRes.data
  } catch (err) {
    console.error('Gagal mengambil supplier', err)
  }
})

onUnmounted(() => {
  window.removeEventListener('click', clickOutsideHandler)
})

async function handleSubmit() {
  // Validate items
  if (form.value.items.some((item: any) => !item.produk_id || !item.qty || !item.harga_beli)) {
    error.value = 'Mohon lengkapi produk, qty, dan harga beli untuk setiap baris.'
    return
  }

  saving.value = true
  error.value = ''

  try {
    const formData = new FormData()
    formData.append('supplier', form.value.supplier)
    formData.append('invoice', form.value.invoice)

    // Map items to clean data required by the backend
    const cleanItems = form.value.items.map((item: any) => ({
      produk_id: item.produk_id,
      qty: Number(item.qty),
      harga_beli: Number(item.harga_beli),
    }))
    formData.append('items', JSON.stringify(cleanItems))

    if (selectedFile.value) {
      formData.append('struk_file', selectedFile.value)
    }

    await pembelianService.create(formData)
    router.push('/pembelian')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal menyimpan pembelian'
  } finally {
    saving.value = false
  }
}
</script>
