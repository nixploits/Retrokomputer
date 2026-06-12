<template>
  <div class="h-full flex flex-col lg:flex-row gap-6 font-mono">
    <!-- Products Panel -->
    <div class="lg:w-[55%] flex flex-col gap-4">
      <div class="bg-white rounded-lg border-2 border-retro-blue p-4 shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-bold text-slate-700 uppercase">CARI BARANG</label>
          <span class="text-[10px] text-slate-400 font-sans"
            >Tekan nama barang untuk memasukkan ke keranjang</span
          >
        </div>
        <input
          ref="searchInput"
          v-model="tempSearchQuery"
          type="text"
          class="w-full px-3 py-2 text-sm border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans"
          placeholder="Cari berdasarkan nama atau kode barang..."
        />
      </div>

      <div class="flex-1 overflow-y-auto min-h-[350px]">
        <div v-if="loadingProducts" class="py-12 text-center text-sm text-slate-400 font-mono">
          MEMUAT PRODUK...
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <button
            v-for="p in filteredProducts"
            :key="p.id"
            @click="addToCart(p)"
            :disabled="p.stok <= 0"
            class="bg-white rounded-lg border-2 border-slate-200 p-3 text-left hover:border-retro-blue hover:bg-slate-50 transition-all disabled:opacity-40 disabled:cursor-not-allowed group relative overflow-hidden shadow-sm"
          >
            <div class="flex justify-between items-start mb-2">
              <span
                class="text-[10px] font-mono font-bold text-retro-blue bg-retro-blue/5 px-1.5 py-0.5 rounded border border-retro-blue/10"
              >
                {{ p.kode_produk }}
              </span>
              <span
                class="text-[9px] font-bold px-1.5 py-0.5 rounded font-mono uppercase"
                :class="
                  p.stok > 0
                    ? 'bg-retro-success-glow text-retro-success border border-retro-success/20'
                    : 'bg-red-50 text-red-600 border border-red-200'
                "
              >
                {{ p.stok > 0 ? `Stok: ${p.stok}` : 'Habis' }}
              </span>
            </div>
            <p
              class="text-xs text-slate-800 font-bold truncate font-sans group-hover:text-retro-blue transition-colors"
            >
              {{ p.nama_produk }}
            </p>
            <p class="text-xs font-bold text-retro-orange-dark mt-2 font-mono">
              {{ formatCurrency(p.harga_jual) }}
            </p>
          </button>
        </div>
        <div
          v-if="!loadingProducts && filteredProducts.length === 0"
          class="py-12 text-center text-sm text-slate-400 font-mono"
        >
          BARANG TIDAK DITEMUKAN
        </div>
      </div>
    </div>

    <!-- Cart Panel -->
    <div class="lg:w-[45%] flex flex-col">
      <div
        class="bg-white rounded-lg border-2 border-retro-blue flex-1 flex flex-col overflow-hidden shadow-sm"
      >
        <!-- Cart Header -->
        <div class="bg-retro-blue text-white px-4 py-3 flex items-center justify-between">
          <span class="font-bold text-xs flex items-center gap-2">
            <span>⊞</span>
            <span>KERANJANG BELANJA</span>
            <span
              v-if="cart.totalItems > 0"
              class="bg-white text-retro-blue text-[9px] font-bold px-1.5 py-0.5 rounded-full font-mono"
            >
              {{ cart.totalItems }}
            </span>
          </span>
          <button
            v-if="cart.items.length > 0"
            @click="cart.clearCart()"
            class="text-[10px] font-bold text-retro-yellow hover:underline uppercase transition-colors"
          >
            Kosongkan
          </button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
          <div
            v-for="item in cart.items"
            :key="item.produk.id"
            class="flex items-center gap-3 p-3 rounded border border-slate-100 bg-slate-50 hover:bg-slate-100/50 transition-colors group"
          >
            <div class="flex-1 min-w-0">
              <p class="text-xs font-bold text-slate-800 truncate font-sans">
                {{ item.produk.nama_produk }}
              </p>
              <p class="text-[10px] text-slate-400 font-mono mt-0.5">
                {{ formatCurrency(item.produk.harga_jual) }}
              </p>
            </div>

            <!-- Interactive Qty Controller -->
            <div class="flex items-center gap-1 shrink-0">
              <button
                @click="updateCartQty(item.produk.id, item.qty - 1)"
                class="w-6 h-6 rounded border border-slate-300 text-slate-600 bg-white hover:bg-slate-100 flex items-center justify-center text-xs font-bold transition-colors"
              >
                −
              </button>

              <!-- Editable Quantity State -->
              <span
                v-if="editingItemId !== item.produk.id"
                @click="startEditQty(item)"
                class="text-xs min-w-[28px] text-center font-bold font-mono cursor-pointer hover:bg-white hover:text-retro-blue hover:border border border-transparent rounded py-0.5 transition-all"
                title="Klik untuk mengubah jumlah secara manual"
              >
                {{ item.qty }}
              </span>

              <input
                v-else
                v-model.number="editQtyValue"
                type="number"
                min="1"
                :max="item.produk.stok"
                @blur="saveQty(item)"
                @keydown.enter="saveQty(item)"
                class="w-12 text-center text-xs border-2 border-retro-blue rounded focus:outline-none font-mono py-0.5 bg-white font-bold"
              />

              <button
                @click="updateCartQty(item.produk.id, item.qty + 1)"
                class="w-6 h-6 rounded border border-slate-300 text-slate-600 bg-white hover:bg-slate-100 flex items-center justify-center text-xs font-bold transition-colors"
              >
                +
              </button>
            </div>

            <!-- Subtotal -->
            <p class="text-xs font-bold text-slate-800 w-24 text-right font-mono shrink-0">
              {{ formatCurrency(item.subtotal) }}
            </p>

            <button
              @click="cart.removeItem(item.produk.id)"
              class="text-slate-300 hover:text-red-500 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity font-bold text-xs"
              title="Hapus"
            >
              ✕
            </button>
          </div>

          <div
            v-if="cart.items.length === 0"
            class="py-12 text-center text-sm text-slate-400 font-mono"
          >
            KERANJANG KOSONG
          </div>
        </div>

        <!-- Checkout Section -->
        <div class="border-t-2 border-slate-200 p-4 space-y-4 bg-slate-50">
          <!-- Nama Pembeli Input -->
          <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-slate-500 uppercase block">NAMA PEMBELI</label>
            <input
              v-model="namaPembeli"
              type="text"
              class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans bg-white font-bold"
              placeholder="Masukkan nama pembeli..."
            />
          </div>

          <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <span class="text-xs font-bold text-slate-600 uppercase">TOTAL PEMBAYARAN</span>
            <span class="text-lg font-bold text-retro-blue font-mono">{{
              formatCurrency(cart.grandTotal)
            }}</span>
          </div>

          <!-- Payment Method -->
          <div>
            <label class="text-[10px] font-bold text-slate-500 uppercase mb-1.5 block"
              >METODE PEMBAYARAN</label
            >
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="m in methods"
                :key="m.value"
                @click="cart.metode_pembayaran = m.value"
                :class="[
                  'py-2 px-2 rounded text-xs border-2 transition-all font-bold uppercase',
                  cart.metode_pembayaran === m.value
                    ? 'bg-retro-blue text-white border-retro-blue shadow-sm'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-100',
                ]"
              >
                {{ m.label }}
              </button>
            </div>
          </div>

          <!-- Uang Diterima & Kembalian (Only for Tunai) -->
          <div
            v-if="cart.metode_pembayaran === 'tunai'"
            class="space-y-2 bg-white p-3 rounded border-2 border-slate-200"
          >
            <div class="flex justify-between items-center">
              <label class="text-[10px] font-bold text-slate-500 uppercase">UANG DITERIMA</label>
              <span class="text-[9px] text-slate-400 font-sans">Nominal uang tunai</span>
            </div>
            <div class="relative">
              <span class="absolute left-3 top-1.5 text-xs font-bold text-slate-400">Rp</span>
              <input
                v-model="displayUangDiterima"
                type="text"
                class="w-full pl-8 pr-3 py-1.5 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono bg-white font-bold"
                placeholder="0"
              />
            </div>
            <div
              v-if="uangDiterima !== null && (uangDiterima as any) !== ''"
              class="flex justify-between items-center text-xs font-bold font-mono pt-1"
            >
              <span class="text-slate-500">KEMBALIAN:</span>
              <span
                :class="uangDiterima >= cart.grandTotal ? 'text-retro-success' : 'text-red-500'"
              >
                {{ formatCurrency(kembalian) }}
              </span>
            </div>
          </div>

          <!-- Checkout Button -->
          <button
            @click="processPayment"
            :disabled="cart.items.length === 0 || processing"
            class="w-full py-2.5 text-sm font-bold text-white bg-retro-blue hover:bg-blue-700 rounded disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm uppercase font-mono"
          >
            {{ processing ? 'MEMPROSES...' : 'BAYAR & SELESAI' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div
      v-if="showSuccess"
      class="fixed inset-0 bg-retro-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div
        class="bg-white border-2 border-retro-blue rounded-lg w-full max-w-xs overflow-hidden shadow-2xl animate-slideUp font-mono"
      >
        <!-- Title bar -->
        <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">TRANSAKSI BERHASIL</span>
          <button
            @click="closeSuccessModal"
            class="text-white hover:text-retro-yellow font-bold text-lg leading-none"
          >
            ×
          </button>
        </div>
        <div class="p-6 text-center">
          <div
            class="w-12 h-12 rounded-full bg-retro-success-glow border-2 border-retro-success flex items-center justify-center mx-auto mb-3 text-retro-success"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              stroke-width="3"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
          </div>
          <h3 class="text-sm font-bold text-slate-800 mb-1">Pembayaran Sukses!</h3>
          <p class="text-[10px] text-slate-400 font-bold uppercase font-mono mb-2">
            {{ lastCode }}
          </p>
          <p class="text-lg font-bold text-retro-orange-dark font-mono mb-4">
            {{ formatCurrency(lastTotal) }}
          </p>
          <div class="flex flex-col gap-2">
            <button
              @click="printLastReceipt"
              class="text-xs font-bold px-5 py-2 bg-retro-orange hover:bg-orange-600 text-white rounded transition-colors uppercase shadow-sm flex items-center justify-center gap-1.5"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.615 0-1.115-.465-1.12-1.08L6 18m11.66 0H6.34m.665-4.171V6.375c0-.621.504-1.125 1.125-1.125h8.25c.621 0 1.125.504 1.125 1.125v7.454M16.5 7.5h.008v.008H16.5V7.5z"
                />
              </svg>
              CETAK NOTA
            </button>
            <button
              @click="closeSuccessModal"
              class="text-xs font-bold px-5 py-2 bg-retro-blue hover:bg-blue-700 text-white rounded transition-colors uppercase shadow-sm"
            >
              Selesai
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from '@/utils/debounce'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import type { Produk } from '@/types'
import { produkService, transaksiService, settingService } from '@/services'
import { printReceipt } from '@/utils/printReceipt'
import { customDialog } from '@/utils/dialog'

const cart = useCartStore()
const authStore = useAuthStore()
const router = useRouter()

const tempSearchQuery = ref('')
const searchQuery = ref('')
const searchInput = ref<HTMLInputElement>()

const updateSearchQuery = debounce((val: string) => {
  searchQuery.value = val
}, 300)

watch(tempSearchQuery, (newVal) => {
  updateSearchQuery(newVal)
})
const showSuccess = ref(false)
const processing = ref(false)
const lastCode = ref('')
const lastTotal = ref(0)
const loadingProducts = ref(true)
const products = ref<Produk[]>([])
const namaPembeli = ref('')

const editingItemId = ref<number | null>(null)
const editQtyValue = ref<number>(0)

// Cash printing states
const uangDiterima = ref<number | null>(null)
const lastTransaksi = ref<any | null>(null)
const logoText = ref('Retro Komputer')

const kembalian = computed(() => {
  if (!uangDiterima.value) return 0
  return Math.max(0, uangDiterima.value - cart.grandTotal)
})

const displayUangDiterima = computed({
  get() {
    if (
      uangDiterima.value === null ||
      uangDiterima.value === undefined ||
      (uangDiterima.value as any) === ''
    )
      return ''
    return new Intl.NumberFormat('id-ID').format(uangDiterima.value)
  },
  set(val: string) {
    const clean = val.replace(/\D/g, '')
    uangDiterima.value = clean ? parseInt(clean, 10) : null
  },
})

watch(
  () => cart.metode_pembayaran,
  (newVal) => {
    if (newVal !== 'tunai') {
      uangDiterima.value = null
    }
  },
)

const methods = [
  { value: 'tunai', label: 'Tunai' },
  { value: 'debit', label: 'Debit' },
  { value: 'transfer', label: 'Transfer' },
]

onMounted(async () => {
  if (authStore.isKasir) {
    try {
      await authStore.fetchActiveKasirProfile()
      if (!authStore.activeKasirProfile) {
        customDialog.warning(
          'Harap aktifkan profil kasir terlebih dahulu pada menu Profil Kasir sebelum melakukan transaksi.',
        )
        router.push('/profil-kasir')
        return
      }
    } catch {
      // ignore
    }
  }

  // Fetch active store settings to get logo_text for receipt
  try {
    const settingRes = await settingService.getActive()
    if (settingRes.data && settingRes.data.logo_text) {
      logoText.value = settingRes.data.logo_text
    }
  } catch {
    // ignore
  }

  try {
    const res = await produkService.getAll()
    products.value = (res.data as Produk[]).filter((p) => p.status === 'aktif')
  } catch {
    /* silent */
  } finally {
    loadingProducts.value = false
  }
})

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value
  const q = searchQuery.value.toLowerCase()
  return products.value.filter(
    (p) => p.nama_produk.toLowerCase().includes(q) || p.kode_produk.toLowerCase().includes(q),
  )
})

function addToCart(p: Produk) {
  try {
    cart.addItem(p)
  } catch (e: any) {
    customDialog.warning(e.message)
  }
}

function updateCartQty(produkId: number, qty: number) {
  try {
    cart.updateQty(produkId, qty)
  } catch (e: any) {
    customDialog.warning(e.message)
  }
}

function startEditQty(item: any) {
  editingItemId.value = item.produk.id
  editQtyValue.value = item.qty
  nextTick(() => {
    const inputs = document.querySelectorAll('input[type="number"]')
    const lastInput = inputs[inputs.length - 1] as HTMLInputElement
    if (lastInput) {
      lastInput.focus()
      lastInput.select()
    }
  })
}

function saveQty(item: any) {
  if (editingItemId.value !== item.produk.id) return

  const val = Number(editQtyValue.value)
  if (isNaN(val) || val <= 0) {
    customDialog.warning('Jumlah barang harus minimal 1.')
    editingItemId.value = null
    return
  }

  try {
    cart.updateQty(item.produk.id, val)
  } catch (e: any) {
    customDialog.warning(e.message)
  } finally {
    editingItemId.value = null
  }
}

async function processPayment() {
  if (cart.items.length === 0) return

  // Validate cash amount if payment method is Tunai
  if (cart.metode_pembayaran === 'tunai') {
    if (
      uangDiterima.value === null ||
      uangDiterima.value === undefined ||
      (uangDiterima.value as any) === ''
    ) {
      customDialog.warning('Harap masukkan nominal uang yang diterima!')
      return
    }
    if (uangDiterima.value < cart.grandTotal) {
      customDialog.warning('Nominal uang diterima kurang dari total pembayaran!')
      return
    }
  }

  processing.value = true
  try {
    const res = await transaksiService.create({
      ...cart.getPayload(),
      nama_pembeli: namaPembeli.value.trim() || undefined,
    })
    lastCode.value = res.data.kode_transaksi
    lastTotal.value = res.data.total
    lastTransaksi.value = res.data

    // Update local stock
    cart.items.forEach((item) => {
      const prod = products.value.find((p) => p.id === item.produk.id)
      if (prod) prod.stok -= item.qty
    })
    cart.clearCart()
    namaPembeli.value = ''
    showSuccess.value = true
  } catch (e: any) {
    customDialog.error(e.response?.data?.message || 'Gagal memproses transaksi')
  } finally {
    processing.value = false
  }
}

function printLastReceipt() {
  if (lastTransaksi.value) {
    printReceipt(lastTransaksi.value, uangDiterima.value, logoText.value)
  }
}

function closeSuccessModal() {
  showSuccess.value = false
  uangDiterima.value = null
  lastTransaksi.value = null
}

function formatCurrency(v: number) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(v)
}
</script>
