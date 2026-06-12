<template>
  <div class="max-w-xl mx-auto font-mono">
    <div class="bg-white rounded-lg border-2 border-retro-blue overflow-hidden shadow-md">
      <!-- Title Bar -->
      <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
        <span class="font-bold text-xs">{{ isEdit ? 'EDIT PRODUK' : 'TAMBAH PRODUK' }}</span>
      </div>

      <div class="p-6 font-sans">
        <div v-if="loadingInit" class="py-8 text-center text-sm text-slate-400 font-mono">
          Memuat...
        </div>

        <form v-else @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
                >Kode Produk</label
              >
              <input
                v-model="form.kode_produk"
                @input="handleKodeProdukInput"
                placeholder="4-5 digit"
                maxlength="5"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori</label>
              <!-- Dropdown or manual category selection -->
              <div class="flex flex-col gap-2">
                <select
                  v-if="!isNewCategory"
                  v-model="selectedCategory"
                  @change="handleCategoryChange"
                  class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue"
                  required
                >
                  <option value="" disabled>-- Pilih Kategori --</option>
                  <option v-for="cat in uniqueCategories" :key="cat" :value="cat">{{ cat }}</option>
                  <option value="__NEW__">-- + Kategori Baru --</option>
                </select>

                <div v-else class="flex gap-2">
                  <input
                    v-model="newCategoryInput"
                    @input="handleNewCategoryInput"
                    placeholder="Nama Kategori Baru"
                    class="flex-1 px-3 py-2 text-sm border-2 border-retro-blue rounded focus:outline-none"
                    required
                  />
                  <button
                    type="button"
                    @click="cancelNewCategory"
                    class="px-2 py-1 text-xs border-2 border-slate-200 rounded hover:bg-slate-50 font-mono"
                    title="Batal"
                  >
                    Batal
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Produk</label>
            <input
              v-model="form.nama_produk"
              @input="handleNamaProdukInput"
              placeholder="Nama Produk (Alfanumerik)"
              class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue"
              required
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
                >Harga Beli (Rp)</label
              >
              <input
                v-model="hargaBeliInput"
                @input="handleHargaBeliInput"
                placeholder="40.000"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
                >Harga Jual (Rp)</label
              >
              <input
                v-model="hargaJualInput"
                @input="handleHargaJualInput"
                placeholder="55.000"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                required
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
                >Stok {{ isEdit ? '(read-only)' : '' }}</label
              >
              <input
                v-model.number="form.stok"
                type="number"
                min="0"
                placeholder="0"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                :disabled="isEdit"
                required
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-700 uppercase mb-1"
                >Stok Minimum</label
              >
              <input
                v-model.number="form.stok_minimum"
                type="number"
                min="0"
                placeholder="0"
                class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                required
              />
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
              to="/produk"
              class="text-xs font-mono font-bold px-3 py-2 rounded border-2 border-slate-200 text-slate-600 hover:bg-slate-50"
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
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { ProdukPayload } from '@/types'
import { produkService } from '@/services'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => !!route.params.id)
const loadingInit = ref(false)
const saving = ref(false)
const error = ref('')

const uniqueCategories = ref<string[]>([])
const isNewCategory = ref(false)
const selectedCategory = ref('')
const newCategoryInput = ref('')

const hargaBeliInput = ref('')
const hargaJualInput = ref('')

const form = ref<any>({
  kode_produk: '',
  nama_produk: '',
  kategori: '',
  harga_beli: '',
  harga_jual: '',
  stok: '',
  stok_minimum: '',
})

function formatRupiah(val: string | number): string {
  const digits = String(val).replace(/\D/g, '')
  if (!digits) return ''
  const truncated = digits.slice(0, 9)
  return new Intl.NumberFormat('id-ID').format(Number(truncated))
}

function handleHargaBeliInput(e: Event) {
  const target = e.target as HTMLInputElement
  const raw = target.value.replace(/\D/g, '').slice(0, 9)
  hargaBeliInput.value = formatRupiah(raw)
  form.value.harga_beli = raw ? Number(raw) : ''
}

function handleHargaJualInput(e: Event) {
  const target = e.target as HTMLInputElement
  const raw = target.value.replace(/\D/g, '').slice(0, 9)
  hargaJualInput.value = formatRupiah(raw)
  form.value.harga_jual = raw ? Number(raw) : ''
}

function handleKodeProdukInput(e: Event) {
  const target = e.target as HTMLInputElement
  form.value.kode_produk = target.value.replace(/[^a-zA-Z0-9]/g, '').slice(0, 5)
}

function handleNamaProdukInput(e: Event) {
  const target = e.target as HTMLInputElement
  form.value.nama_produk = target.value.replace(/[^a-zA-Z0-9\s]/g, '')
}

function handleNewCategoryInput(e: Event) {
  const target = e.target as HTMLInputElement
  newCategoryInput.value = target.value.replace(/[^a-zA-Z0-9\s]/g, '')
  form.value.kategori = newCategoryInput.value
}

function handleCategoryChange() {
  if (selectedCategory.value === '__NEW__') {
    isNewCategory.value = true
    form.value.kategori = ''
  } else {
    isNewCategory.value = false
    form.value.kategori = selectedCategory.value
  }
}

function cancelNewCategory() {
  isNewCategory.value = false
  newCategoryInput.value = ''
  selectedCategory.value = ''
  form.value.kategori = ''
}

onMounted(async () => {
  loadingInit.value = true
  try {
    // 1. Fetch unique categories from existing products
    const pRes = await produkService.getAll()
    const cats = pRes.data.map((p: any) => p.kategori)
    uniqueCategories.value = Array.from(new Set(cats)).filter(Boolean) as string[]

    // 2. Fetch product details if editing
    if (isEdit.value) {
      const res = await produkService.getById(Number(route.params.id))
      const p = res.data
      form.value = {
        kode_produk: p.kode_produk,
        nama_produk: p.nama_produk,
        kategori: p.kategori,
        harga_beli: Math.round(Number(p.harga_beli)),
        harga_jual: Math.round(Number(p.harga_jual)),
        stok: p.stok,
        stok_minimum: p.stok_minimum,
      }
      hargaBeliInput.value = formatRupiah(Math.round(Number(p.harga_beli)))
      hargaJualInput.value = formatRupiah(Math.round(Number(p.harga_jual)))

      // select category if it exists, otherwise open input
      if (uniqueCategories.value.includes(p.kategori)) {
        selectedCategory.value = p.kategori
      } else {
        isNewCategory.value = true
        newCategoryInput.value = p.kategori
      }
    }
  } catch (err) {
    error.value = 'Gagal memuat data awal'
  } finally {
    loadingInit.value = false
  }
})

async function handleSubmit() {
  saving.value = true
  error.value = ''

  // Validate kode_produk length
  if (form.value.kode_produk.length < 4 || form.value.kode_produk.length > 5) {
    error.value = 'Kode produk harus terdiri dari 4 sampai 5 karakter alfanumerik.'
    saving.value = false
    return
  }

  // Validate special characters (redundant security check)
  const alphaNumericRegex = /^[a-zA-Z0-9\s]+$/
  if (!alphaNumericRegex.test(form.value.kode_produk)) {
    error.value = 'Kode produk tidak boleh mengandung karakter spesial.'
    saving.value = false
    return
  }
  if (!alphaNumericRegex.test(form.value.nama_produk)) {
    error.value = 'Nama produk tidak boleh mengandung karakter spesial.'
    saving.value = false
    return
  }
  if (!alphaNumericRegex.test(form.value.kategori)) {
    error.value = 'Kategori tidak boleh mengandung karakter spesial.'
    saving.value = false
    return
  }

  // Assemble final payload
  const payload: ProdukPayload = {
    kode_produk: form.value.kode_produk,
    nama_produk: form.value.nama_produk,
    kategori: form.value.kategori,
    harga_beli: Number(form.value.harga_beli) || 0,
    harga_jual: Number(form.value.harga_jual) || 0,
    stok: Number(form.value.stok) || 0,
    stok_minimum: Number(form.value.stok_minimum) || 0,
  }

  try {
    if (isEdit.value) {
      await produkService.update(Number(route.params.id), payload)
    } else {
      await produkService.create(payload)
    }
    router.push('/produk')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal menyimpan produk'
  } finally {
    saving.value = false
  }
}
</script>
