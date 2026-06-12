<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Panel -->
    <div class="lg:col-span-1 bg-white rounded-lg border border-slate-200 p-5 space-y-4 shadow-sm self-start">
      <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
        <span class="text-retro-blue text-lg">■</span>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">
          {{ isEditing ? 'Edit Supplier' : 'Tambah Supplier' }}
        </h3>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Supplier</label>
          <input
            v-model="form.nama"
            type="text"
            class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-retro-blue focus:border-retro-blue"
            placeholder="CV. Retro Jaya"
            required
          />
        </div>

        <!-- Telepon -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Telepon</label>
          <input
            v-model="form.telepon"
            type="text"
            @input="form.telepon = (form.telepon || '').replace(/\D/g, '').slice(0, 13)"
            class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-retro-blue focus:border-retro-blue"
            placeholder="08123456789"
            maxlength="13"
          />
        </div>

        <!-- Alamat -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat</label>
          <textarea
            v-model="form.alamat"
            rows="3"
            maxlength="255"
            class="w-full px-3 py-1.5 text-xs border border-slate-300 rounded focus:outline-none focus:ring-2 focus:ring-retro-blue focus:border-retro-blue"
            placeholder="Jl. Komputer Kuno No. 8..."
          ></textarea>
          <div class="text-[10px] text-slate-400 text-right mt-0.5">
            {{ form.alamat ? form.alamat.length : 0 }}/255 karakter
          </div>
        </div>

        <div v-if="error" class="p-2.5 rounded-md bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <div class="flex gap-2">
          <button
            v-if="isEditing"
            type="button"
            @click="cancelEdit"
            class="flex-1 py-2 text-xs font-bold border border-slate-300 text-slate-700 rounded hover:bg-slate-50 transition-colors"
          >
            Batal
          </button>
          <button
            :disabled="saving"
            type="submit"
            class="flex-1 py-2 text-xs font-bold text-white bg-retro-blue hover:bg-blue-700 rounded transition-colors shadow-sm disabled:opacity-50"
          >
            {{ saving ? 'Menyimpan...' : (isEditing ? 'Perbarui' : 'Simpan') }}
          </button>
        </div>
      </form>
    </div>

    <!-- List Panel -->
    <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm flex flex-col">
      <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Daftar Supplier Resmi</h3>
        <span class="text-[10px] text-slate-400 font-mono">Total: {{ list.length }} supplier</span>
      </div>

      <div class="overflow-x-auto flex-1">
        <div v-if="loading" class="p-8 text-center text-xs text-slate-400">
          Memuat data supplier...
        </div>
        <table v-else class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50/50 text-slate-500 border-b border-slate-200">
              <th class="py-2.5 px-4 font-semibold">Nama</th>
              <th class="py-2.5 px-4 font-semibold">Telepon</th>
              <th class="py-2.5 px-4 font-semibold">Alamat</th>
              <th class="py-2.5 px-4 font-semibold text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-if="list.length === 0">
              <td colspan="4" class="py-8 text-center text-slate-400 font-medium">Belum ada supplier yang terdaftar.</td>
            </tr>
            <tr v-for="item in list" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="py-3 px-4 font-medium text-slate-800">{{ item.nama }}</td>
              <td class="py-3 px-4 font-mono text-slate-600">{{ item.telepon || '-' }}</td>
              <td class="py-3 px-4 text-slate-500 max-w-[250px] truncate" :title="item.alamat">{{ item.alamat || '-' }}</td>
              <td class="py-3 px-4 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <button @click="startEdit(item)" class="text-xs px-2 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-100">
                    Edit
                  </button>
                  <button @click="confirmDelete(item)" class="text-xs px-2 py-1 rounded border border-slate-200 text-red-500 hover:bg-red-50">
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="deleteTarget" class="fixed inset-0 bg-retro-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="deleteTarget = null">
      <div class="bg-white border-2 border-red-600 rounded-lg max-w-sm w-full overflow-hidden shadow-2xl animate-slideUp font-mono">
        <!-- Title bar -->
        <div class="bg-red-600 text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">HAPUS SUPPLIER</span>
          <button @click="deleteTarget = null" class="text-white hover:text-retro-yellow transition-colors font-bold text-lg leading-none">×</button>
        </div>
        <div class="p-6 font-sans">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded bg-red-50 border border-red-200 flex items-center justify-center shrink-0 text-red-500 text-xl font-bold">!</div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">Konfirmasi Hapus</h3>
              <p class="text-xs text-slate-500 leading-relaxed">
                Apakah Anda yakin ingin menghapus supplier <strong class="text-slate-800">{{ deleteTarget.nama }}</strong>? Tindakan ini akan menghapusnya secara permanen dari sistem.
              </p>
            </div>
          </div>
        </div>
        <div class="bg-slate-50 px-4 py-3 flex justify-end gap-2 border-t border-slate-100">
          <button @click="deleteTarget = null" class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded font-sans transition-colors">
            Batal
          </button>
          <button @click="doDelete" class="px-3 py-1.5 text-xs bg-red-600 hover:bg-red-700 text-white rounded font-sans font-semibold transition-colors shadow-sm">
            Ya, Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Supplier, SupplierPayload } from '@/types'
import { supplierService } from '@/services'
import { customDialog } from '@/utils/dialog'

const list = ref<Supplier[]>([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')

const isEditing = ref(false)
const editingId = ref<number | null>(null)
const deleteTarget = ref<Supplier | null>(null)

const form = ref<SupplierPayload>({
  nama: '',
  telepon: '',
  alamat: '',
})

onMounted(async () => {
  await fetchSuppliers()
})

async function fetchSuppliers() {
  loading.value = true
  try {
    const res = await supplierService.getAll()
    list.value = res.data
  } catch (err) {
    console.error('Gagal mengambil data supplier:', err)
  } finally {
    loading.value = false
  }
}

function startEdit(item: Supplier) {
  isEditing.value = true
  editingId.value = item.id
  form.value = {
    nama: item.nama,
    telepon: item.telepon || '',
    alamat: item.alamat || '',
  }
  error.value = ''
}

function cancelEdit() {
  isEditing.value = false
  editingId.value = null
  resetForm()
}

function resetForm() {
  form.value = {
    nama: '',
    telepon: '',
    alamat: '',
  }
  error.value = ''
}

async function handleSubmit() {
  // Validate alphanumeric / no special chars in name
  if (/[^a-zA-Z0-9\s\.\,\-]/.test(form.value.nama)) {
    error.value = 'Nama supplier tidak boleh menggunakan karakter spesial!'
    return
  }

  // Validate telepon: only digits and max 13 digits
  if (form.value.telepon && (!/^\d+$/.test(form.value.telepon) || form.value.telepon.length > 13)) {
    error.value = 'Nomor telepon harus berupa angka dengan maksimal 13 digit!'
    return
  }

  // Validate alamat: max 255 chars
  if (form.value.alamat && form.value.alamat.length > 255) {
    error.value = 'Alamat maksimal hanya 255 karakter!'
    return
  }

  saving.value = true
  error.value = ''
  try {
    if (isEditing.value && editingId.value !== null) {
      await supplierService.update(editingId.value, form.value)
      isEditing.value = false
      editingId.value = null
    } else {
      await supplierService.create(form.value)
    }
    resetForm()
    await fetchSuppliers()
  } catch (err: unknown) {
    let errorMsg = 'Gagal menyimpan supplier.'
    if (err && typeof err === 'object' && 'response' in err) {
      const response = (err as Record<string, unknown>).response
      if (response && typeof response === 'object' && 'data' in response) {
        const data = response.data as Record<string, unknown>
        if (data.message) errorMsg = String(data.message)
      }
    }
    error.value = errorMsg
  } finally {
    saving.value = false
  }
}

function confirmDelete(item: Supplier) {
  deleteTarget.value = item
}

async function doDelete() {
  if (!deleteTarget.value) return
  try {
    await supplierService.delete(deleteTarget.value.id)
    await fetchSuppliers()
  } catch {
    customDialog.error('Gagal menghapus supplier.')
  } finally {
    deleteTarget.value = null
  }
}
</script>
