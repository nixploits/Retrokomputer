<template>
  <div class="space-y-6 font-mono">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div
          class="w-1 h-8 bg-gradient-to-b from-retro-orange to-retro-orange/50 rounded-full"
        ></div>
        <h2 class="text-base font-bold text-slate-800 dark:text-white uppercase tracking-wider">
          PENGELOLAAN PROFIL KASIR
        </h2>
      </div>
      <div
        class="text-xs text-slate-400 font-bold bg-slate-100 px-2 py-1 border border-slate-200 rounded"
      >
        Role: {{ authStore.user?.role?.toUpperCase() }}
      </div>
    </div>

    <!-- ADMIN VIEW -->
    <div v-if="authStore.isAdmin" class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fadeIn">
      <!-- Admin Form (1 column) -->
      <div class="lg:col-span-1 space-y-4">
        <div class="bg-white border-2 border-retro-blue rounded-lg overflow-hidden shadow-sm">
          <div class="bg-retro-blue text-white px-4 py-2.5 text-xs font-bold uppercase">
            {{ isEditing ? 'Edit Profil Kasir' : 'Tambah Profil Kasir' }}
          </div>

          <form @submit.prevent="handleSubmit" class="p-4 space-y-4">
            <!-- Akun Kasir Dropdown (Only shown and editable when adding) -->
            <div class="space-y-1.5" v-if="!isEditing">
              <label class="text-[10px] font-bold text-slate-650 uppercase">AKUN KASIR</label>
              <select
                v-model="form.user_id"
                required
                class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans bg-white"
              >
                <option value="" disabled>-- Pilih User Kasir --</option>
                <option v-for="u in kasirUsers" :key="u.id" :value="u.id">
                  {{ u.name }} (@{{ u.username }})
                </option>
              </select>
            </div>

            <div class="space-y-1.5" v-else>
              <label class="text-[10px] font-bold text-slate-650 uppercase block">AKUN KASIR</label>
              <div
                class="w-full px-3 py-2 text-xs border-2 border-slate-100 rounded bg-slate-50 font-sans text-slate-550 font-medium"
              >
                {{ editingProfileUser }}
              </div>
            </div>

            <!-- Nama -->
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold text-slate-650 uppercase"
                >NAMA PROFIL KASIR</label
              >
              <input
                v-model="form.nama"
                type="text"
                required
                class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans"
                placeholder="Contoh: Budi Prasetyo"
                maxlength="255"
              />
            </div>

            <!-- Kode Khusus / PIN -->
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold text-slate-655 uppercase">
                KODE KHUSUS / PIN {{ isEditing ? '(OPSIONAL)' : '' }}
              </label>
              <input
                v-model="form.kode_khusus"
                type="password"
                :required="!isEditing"
                class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                :placeholder="
                  isEditing
                    ? 'Biarkan kosong jika tidak ingin diubah'
                    : 'Masukkan kode PIN khusus...'
                "
                maxlength="255"
              />
              <span class="text-[9px] text-slate-400 font-sans block mt-1 leading-normal">
                Kode ini digunakan oleh kasir bersangkutan untuk mengaktifkan profil saat bertugas.
              </span>
            </div>

            <div class="flex gap-2 font-sans pt-2">
              <button
                v-if="isEditing"
                type="button"
                @click="cancelEdit"
                class="flex-1 py-2 text-xs font-bold border-2 border-slate-200 text-slate-600 hover:bg-slate-55 rounded transition-colors uppercase"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="processing"
                class="flex-1 py-2 text-xs font-bold text-white bg-retro-blue hover:bg-blue-700 rounded transition-colors uppercase shadow-sm"
              >
                {{ processing ? 'PROSES...' : isEditing ? 'PERBARUI' : 'TAMBAH' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Admin List Panel (2 columns) -->
      <div
        class="lg:col-span-2 bg-white rounded-lg border-2 border-slate-200 overflow-hidden shadow-sm flex flex-col"
      >
        <div
          class="p-4 border-b-2 border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50"
        >
          <h3
            class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 flex items-center gap-2"
          >
            <span class="inline-block w-2 h-2 bg-retro-orange rounded-full"></span>
            DAFTAR SEMUA PROFIL KASIR
          </h3>
          <span class="text-[10px] text-slate-400 font-mono"
            >Total: {{ profiles.length }} profil</span
          >
        </div>

        <div class="overflow-x-auto flex-1">
          <div v-if="loading" class="p-8 text-center text-xs text-slate-400">
            Memuat data profil kasir...
          </div>
          <table v-else class="w-full text-left text-xs border-collapse font-mono">
            <thead>
              <tr
                class="bg-slate-100/80 text-slate-650 border-b-2 border-slate-200 uppercase tracking-wider text-[10px]"
              >
                <th class="py-3 px-4 font-bold">Akun Kasir</th>
                <th class="py-3 px-4 font-bold">Nama Profil</th>
                <th class="py-3 px-4 font-bold text-center">Status</th>
                <th class="py-3 px-4 font-bold text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y-2 divide-slate-100 text-slate-700">
              <tr v-if="profiles.length === 0">
                <td colspan="4" class="py-8 text-center text-slate-450 font-medium">
                  Belum ada profil kasir yang terdaftar.
                </td>
              </tr>
              <tr v-for="p in profiles" :key="p.id" class="hover:bg-slate-55/50 transition-colors">
                <td class="py-3.5 px-4 font-sans text-slate-800">
                  <span class="font-bold">{{ p.user?.name || '-' }}</span>
                  <span class="text-xs text-slate-400 block font-mono"
                    >@{{ p.user?.username || '-' }}</span
                  >
                </td>
                <td class="py-3.5 px-4 font-medium text-slate-800 font-sans">{{ p.nama }}</td>
                <td class="py-3.5 px-4 text-center">
                  <span
                    :class="[
                      'text-[9px] font-bold px-2 py-0.5 rounded border uppercase',
                      p.is_active
                        ? 'bg-emerald-50 text-emerald-600 border-emerald-200'
                        : 'bg-slate-50 text-slate-400 border-slate-200',
                    ]"
                  >
                    {{ p.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                  <div class="flex items-center justify-center gap-2">
                    <button
                      @click="startEdit(p)"
                      class="text-xs text-retro-blue hover:text-blue-700 font-bold hover:underline"
                    >
                      Edit
                    </button>
                    <button
                      @click="confirmDelete(p)"
                      class="text-xs text-red-500 hover:text-red-700 font-bold hover:underline"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- KASIR VIEW -->
    <div v-else-if="authStore.isKasir" class="space-y-6 animate-fadeIn">
      <!-- Active Profile Banner -->
      <div
        v-if="authStore.activeKasirProfile"
        class="bg-emerald-50 border-2 border-emerald-500 text-emerald-800 p-4 rounded-lg flex items-center justify-between shadow-sm animate-fadeIn"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-8 h-8 rounded bg-emerald-500 text-white flex items-center justify-center font-bold text-sm"
          >
            ✓
          </div>
          <div>
            <p class="text-xs font-bold uppercase tracking-wider">PROFIL KASIR AKTIF</p>
            <p class="text-sm font-black">{{ authStore.activeKasirProfile.nama }}</p>
          </div>
        </div>
        <button
          @click="deactivateProfile"
          :disabled="processing"
          class="px-3 py-1.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded transition-colors uppercase"
        >
          Nonaktifkan
        </button>
      </div>

      <div
        v-else
        class="bg-amber-50 border-2 border-amber-400 text-amber-800 p-4 rounded-lg flex items-center gap-3 shadow-sm"
      >
        <div
          class="w-8 h-8 rounded bg-amber-400 text-white flex items-center justify-center font-bold text-sm"
        >
          !
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider">BELUM ADA PROFIL AKTIF</p>
          <p class="text-xs font-sans text-amber-700 leading-relaxed mt-0.5">
            Anda harus memilih dan mengaktifkan salah satu profil kasir di bawah sebelum dapat
            mengakses menu Kasir POS atau memproses transaksi.
          </p>
        </div>
      </div>

      <!-- Profiles List -->
      <div class="bg-white border-2 border-slate-200 rounded-lg overflow-hidden shadow-sm">
        <div
          class="bg-slate-100 dark:bg-slate-800 border-b-2 border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 flex justify-between items-center"
        >
          <span class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 bg-retro-orange rounded-full"></span>
            DAFTAR PROFIL KASIR SAYA
          </span>
          <span class="text-[10px] text-slate-400 dark:text-slate-400 font-sans"
            >Pilih dan aktifkan profil untuk mulai bekerja</span
          >
        </div>

        <div class="p-4">
          <div v-if="loading" class="py-12 text-center text-sm text-slate-400 font-mono">
            Memuat profil kasir...
          </div>

          <div
            v-else-if="profiles.length === 0"
            class="py-12 text-center text-sm text-slate-400 font-mono"
          >
            Belum ada profil kasir untuk akun Anda. Silakan hubungi Admin untuk menambahkan profil
            baru.
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 font-mono">
            <div
              v-for="p in profiles"
              :key="p.id"
              :class="[
                'border-2 rounded-lg p-4 transition-all duration-150 flex flex-col justify-between h-36 shadow-sm relative overflow-hidden bg-white',
                p.is_active
                  ? 'border-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.1)]'
                  : 'border-slate-200 hover:border-retro-blue',
              ]"
            >
              <!-- Badge status -->
              <div class="absolute top-0 right-0">
                <span
                  :class="[
                    'text-[9px] font-bold px-2 py-0.5 rounded-bl uppercase border-l border-b',
                    p.is_active
                      ? 'bg-emerald-500 text-white border-emerald-500'
                      : 'bg-slate-100 text-slate-400 border-slate-200',
                  ]"
                >
                  {{ p.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>

              <!-- Profile Info -->
              <div class="space-y-1 pr-12">
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider"
                  >KASIR #{{ p.id }}</span
                >
                <h3 class="text-sm font-black text-slate-800 truncate font-sans">{{ p.nama }}</h3>
                <p class="text-[10px] text-slate-400">PIN: ****</p>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-2">
                <button
                  v-if="!p.is_active"
                  @click="openActivationModal(p)"
                  class="px-2.5 py-1 text-xs font-bold bg-retro-blue hover:bg-blue-700 text-white rounded transition-colors uppercase ml-auto"
                >
                  Aktifkan
                </button>
                <span
                  v-else
                  class="text-xs font-bold text-emerald-600 flex items-center gap-1 ml-auto"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                  AKTIF
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Activation PIN Modal -->
    <div
      v-if="showPinModal"
      class="fixed inset-0 bg-retro-dark/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fadeIn"
    >
      <div
        class="bg-white border-2 border-retro-blue rounded-lg w-full max-w-sm overflow-hidden shadow-2xl font-mono"
      >
        <div class="bg-retro-blue text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs">■ VERIFIKASI KODE KHUSUS</span>
          <button
            @click="closePinModal"
            class="text-white hover:text-retro-yellow font-bold text-lg leading-none"
          >
            ×
          </button>
        </div>

        <form @submit.prevent="submitActivation" class="p-6 space-y-4">
          <div class="text-center mb-2">
            <h3 class="text-sm font-bold text-slate-800 font-sans">
              Aktifkan Profil: {{ targetProfile?.nama }}
            </h3>
            <p class="text-[10px] text-slate-400 font-sans mt-0.5">
              Masukkan kode khusus / PIN kasir untuk verifikasi
            </p>
          </div>

          <div class="space-y-1.5">
            <input
              ref="pinInputRef"
              v-model="activationPin"
              type="password"
              required
              class="w-full px-3 py-2.5 text-center text-lg tracking-widest border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
              placeholder="••••••"
              maxlength="255"
            />
          </div>

          <div v-if="pinError" class="text-xs text-red-500 text-center font-bold font-sans">
            {{ pinError }}
          </div>

          <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 mt-4 font-sans">
            <button
              type="button"
              @click="closePinModal"
              class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-850 bg-white border border-slate-300 rounded transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-4 py-1.5 text-xs bg-retro-blue hover:bg-blue-700 text-white rounded font-semibold transition-colors shadow-sm uppercase"
            >
              {{ processing ? 'MEMPROSES...' : 'VERIFIKASI & AKTIFKAN' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import type { ProfilKasir } from '@/types'
import { profilKasirService } from '@/services'
import { toast } from '@/utils/toast'
import { customDialog } from '@/utils/dialog'

const authStore = useAuthStore()

const loading = ref(true)
const processing = ref(false)
const profiles = ref<ProfilKasir[]>([])
const kasirUsers = ref<{ id: number; name: string; username: string }[]>([])

// Add/Edit Form State
const isEditing = ref(false)
const editingProfileId = ref<number | null>(null)
const editingProfileUser = ref('')
const form = ref({
  user_id: '' as number | '',
  nama: '',
  kode_khusus: '',
})

// PIN Modal State (for Kasir activation)
const showPinModal = ref(false)
const targetProfile = ref<ProfilKasir | null>(null)
const activationPin = ref('')
const pinError = ref('')
const pinInputRef = ref<HTMLInputElement | null>(null)

async function fetchProfiles() {
  loading.value = true
  try {
    const res = await profilKasirService.getAll()
    profiles.value = res.data
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memuat profil kasir.')
  } finally {
    loading.value = false
  }
}

async function fetchKasirUsers() {
  try {
    const res = await profilKasirService.getKasirUsers()
    kasirUsers.value = res.data
  } catch (err: any) {
    console.error('Gagal memuat user kasir:', err)
  }
}

onMounted(async () => {
  await fetchProfiles()
  if (authStore.isAdmin) {
    await fetchKasirUsers()
  }
})

function startEdit(p: ProfilKasir) {
  isEditing.value = true
  editingProfileId.value = p.id
  editingProfileUser.value = p.user ? `${p.user.name} (@${p.user.username})` : '-'
  form.value.nama = p.nama
  form.value.kode_khusus = ''
  form.value.user_id = p.user_id
}

function cancelEdit() {
  isEditing.value = false
  editingProfileId.value = null
  editingProfileUser.value = ''
  resetForm()
}

function resetForm() {
  form.value.user_id = ''
  form.value.nama = ''
  form.value.kode_khusus = ''
}

async function handleSubmit() {
  if (processing.value) return
  processing.value = true
  try {
    if (isEditing.value && editingProfileId.value !== null) {
      // Update
      const payload: { nama: string; kode_khusus?: string } = {
        nama: form.value.nama,
      }
      if (form.value.kode_khusus) {
        payload.kode_khusus = form.value.kode_khusus
      }
      await profilKasirService.update(editingProfileId.value, payload)
      customDialog.success('Profil kasir berhasil diperbarui.')
      cancelEdit()
    } else {
      // Create
      if (form.value.user_id === '') {
        customDialog.warning('Silakan pilih user kasir.')
        return
      }
      await profilKasirService.create({
        user_id: form.value.user_id,
        nama: form.value.nama,
        kode_khusus: form.value.kode_khusus,
      })
      customDialog.success('Profil kasir berhasil ditambahkan.')
      resetForm()
    }
    await fetchProfiles()
  } catch (err: any) {
    customDialog.error(err.response?.data?.message || 'Gagal memproses profil kasir.')
  } finally {
    processing.value = false
  }
}

async function confirmDelete(p: ProfilKasir) {
  const confirmed = await customDialog.confirm(
    `Apakah Anda yakin ingin menghapus profil kasir "${p.nama}"?`,
  )
  if (!confirmed) {
    return
  }

  try {
    await profilKasirService.delete(p.id)
    if (authStore.activeKasirProfile?.id === p.id) {
      authStore.activeKasirProfile = null
    }
    await fetchProfiles()
    customDialog.success('Profil kasir berhasil dihapus.')
  } catch (err: any) {
    customDialog.error(err.response?.data?.message || 'Gagal menghapus profil kasir.')
  }
}

function openActivationModal(p: ProfilKasir) {
  targetProfile.value = p
  activationPin.value = ''
  pinError.value = ''
  showPinModal.value = true
  nextTick(() => {
    if (pinInputRef.value) {
      pinInputRef.value.focus()
    }
  })
}

function closePinModal() {
  showPinModal.value = false
  targetProfile.value = null
  activationPin.value = ''
  pinError.value = ''
}

async function submitActivation() {
  if (!targetProfile.value || processing.value) return
  processing.value = true
  pinError.value = ''
  try {
    const res = await profilKasirService.aktifkan(targetProfile.value.id, activationPin.value)
    authStore.activeKasirProfile = res.data
    closePinModal()
    await fetchProfiles()
    toast.success(`Profil kasir "${res.data.nama}" telah diaktifkan!`)
  } catch (err: any) {
    pinError.value = err.response?.data?.message || 'Gagal mengaktifkan profil kasir.'
  } finally {
    processing.value = false
  }
}

async function deactivateProfile() {
  if (processing.value) return
  processing.value = true
  try {
    await profilKasirService.nonaktifkan()
    authStore.activeKasirProfile = null
    await fetchProfiles()
    toast.success('Profil kasir dinonaktifkan.')
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal menonaktifkan profil kasir.')
  } finally {
    processing.value = false
  }
}
</script>
