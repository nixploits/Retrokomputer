<template>
  <div class="space-y-6 font-mono max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-xl font-bold text-retro-blue">■</span>
        <h2 class="text-base font-bold text-slate-800 uppercase tracking-wider">
          PENGATURAN PROFIL
        </h2>
      </div>
      <div
        class="text-xs text-slate-400 font-bold bg-slate-100 px-2 py-1 border border-slate-200 rounded"
      >
        Role: {{ authStore.user?.role?.toUpperCase() }}
      </div>
    </div>

    <!-- 1. VERIFICATION SCREEN -->
    <div
      v-if="!verified"
      class="bg-white border-2 border-retro-blue rounded-lg overflow-hidden shadow-sm animate-fadeIn"
    >
      <div
        class="bg-retro-blue text-white px-4 py-3 text-xs font-bold uppercase flex items-center gap-2"
      >
        <span>🔒</span> VERIFIKASI KEAMANAN DIPERLUKAN
      </div>

      <div class="p-6 space-y-6">
        <div class="text-center max-w-md mx-auto space-y-2">
          <div
            class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl border border-slate-200"
          >
            🛡️
          </div>
          <h3 class="text-sm font-black text-slate-800">Verifikasi Identitas Anda</h3>
          <p class="text-xs text-slate-500 font-sans leading-relaxed">
            Sebelum dapat mengubah email atau kata sandi, Anda perlu memverifikasi identitas
            terlebih dahulu menggunakan salah satu metode di bawah ini.
          </p>
        </div>

        <!-- Method Tabs -->
        <div class="flex border-b border-slate-200 text-xs">
          <button
            @click="verificationMethod = 'password'"
            :class="[
              'flex-1 py-3 text-center font-bold border-b-2 transition-all',
              verificationMethod === 'password'
                ? 'border-retro-blue text-retro-blue bg-blue-50/30'
                : 'border-transparent text-slate-400 hover:text-slate-655',
            ]"
          >
            🔑 KATA SANDI
          </button>
          <button
            @click="verificationMethod = 'email'"
            :class="[
              'flex-1 py-3 text-center font-bold border-b-2 transition-all',
              verificationMethod === 'email'
                ? 'border-retro-blue text-retro-blue bg-blue-50/30'
                : 'border-transparent text-slate-400 hover:text-slate-655',
            ]"
          >
            ✉️ OTP EMAIL
          </button>
        </div>

        <!-- Tab Content: Password -->
        <div v-if="verificationMethod === 'password'" class="space-y-4 max-w-sm mx-auto">
          <form @submit.prevent="handlePasswordVerify" class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold text-slate-650 uppercase"
                >KATA SANDI SAAT INI</label
              >
              <input
                v-model="passwordForm.password"
                type="password"
                required
                class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans"
                placeholder="Masukkan kata sandi Anda..."
              />
            </div>

            <button
              type="submit"
              :disabled="processing"
              class="w-full py-2.5 text-xs font-bold text-white bg-retro-blue hover:bg-blue-700 rounded transition-colors uppercase shadow-sm"
            >
              {{ processing ? 'Memverifikasi...' : 'VERIFIKASI SEKARANG' }}
            </button>
          </form>
        </div>

        <!-- Tab Content: Email OTP -->
        <div v-else class="space-y-4 max-w-sm mx-auto">
          <!-- State A: Send OTP Button -->
          <div v-if="!otpSent" class="space-y-4 text-center">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded text-left">
              <p class="text-[10px] font-bold text-slate-400 uppercase">EMAIL TERDAFTAR</p>
              <p class="text-xs font-black text-slate-700 font-sans mt-0.5">
                {{ maskedEmail || 'Memuat email...' }}
              </p>
            </div>

            <button
              @click="handleSendOtp"
              :disabled="processing || !maskedEmail"
              class="w-full py-2.5 text-xs font-bold text-white bg-retro-blue hover:bg-blue-700 rounded transition-colors uppercase shadow-sm"
            >
              {{ processing ? 'Mengirim OTP...' : 'KIRIM KODE OTP KE EMAIL' }}
            </button>
          </div>

          <!-- State B: OTP Verification Input -->
          <div v-else class="space-y-4">
            <div
              class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-3 rounded text-xs font-sans leading-relaxed"
            >
              Kode OTP telah dikirim ke email <strong>{{ maskedEmail }}</strong
              >. Silakan periksa kotak masuk atau folder spam Anda.
            </div>

            <form @submit.prevent="handleOtpVerify" class="space-y-4">
              <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-650 uppercase block text-center"
                  >MASUKKAN 6 DIGIT KODE OTP</label
                >
                <input
                  v-model="otpForm.code"
                  type="text"
                  required
                  maxlength="6"
                  class="w-full px-3 py-2.5 text-center text-lg tracking-widest border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-mono"
                  placeholder="000000"
                />
              </div>

              <button
                type="submit"
                :disabled="processing"
                class="w-full py-2.5 text-xs font-bold text-white bg-retro-blue hover:bg-blue-700 rounded transition-colors uppercase shadow-sm"
              >
                {{ processing ? 'Memverifikasi...' : 'VERIFIKASI OTP' }}
              </button>

              <div class="text-center">
                <button
                  type="button"
                  @click="handleSendOtp"
                  :disabled="processing"
                  class="text-[10px] font-bold text-retro-blue hover:underline uppercase"
                >
                  Kirim Ulang Kode OTP
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. PROFILE EDIT FORM (SHOWN AFTER VERIFICATION) -->
    <div
      v-else
      class="bg-white border-2 border-emerald-500 rounded-lg overflow-hidden shadow-sm animate-fadeIn"
    >
      <div
        class="bg-emerald-500 text-white px-4 py-3 text-xs font-bold uppercase flex items-center justify-between"
      >
        <span>🔓 AKSES DIIZINKAN - UBAH PROFIL</span>
        <span class="text-[9px] bg-emerald-600 px-2 py-0.5 rounded text-white font-normal"
          >Sesi Aktif</span
        >
      </div>

      <div class="p-6">
        <form @submit.prevent="handleProfileUpdate" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nama Lengkap (Read-only / info) -->
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold text-slate-400 uppercase">NAMA LENGKAP</label>
              <div
                class="w-full px-3 py-2 text-xs border-2 border-slate-100 rounded bg-slate-50 font-sans text-slate-500"
              >
                {{ profileInfo.name }}
              </div>
            </div>

            <!-- Username (Read-only / info) -->
            <div class="space-y-1.5">
              <label class="text-[10px] font-bold text-slate-400 uppercase">USERNAME</label>
              <div
                class="w-full px-3 py-2 text-xs border-2 border-slate-100 rounded bg-slate-50 font-sans text-slate-500"
              >
                @{{ profileInfo.username }}
              </div>
            </div>
          </div>

          <!-- Email Baru -->
          <div class="space-y-1.5">
            <label class="text-[10px] font-bold text-slate-650 uppercase">EMAIL PENGGUNA</label>
            <input
              v-model="editForm.email"
              type="email"
              required
              class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans bg-white text-slate-800"
              placeholder="contoh@retrokomputer.com"
            />
            <span class="text-[9px] text-slate-400 font-sans block leading-normal">
              Email ini digunakan untuk verifikasi keamanan dan keperluan sistem lainnya.
            </span>
          </div>

          <div class="border-t border-slate-100 pt-4 space-y-4">
            <h3 class="text-xs font-black text-slate-700 uppercase">
              ■ UBAH KATA SANDI (OPSIONAL)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Password Baru -->
              <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-655 uppercase"
                  >KATA SANDI BARU</label
                >
                <input
                  v-model="editForm.password"
                  type="password"
                  class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans bg-white"
                  placeholder="Minimal 6 karakter..."
                />
              </div>

              <!-- Konfirmasi Password Baru -->
              <div class="space-y-1.5">
                <label class="text-[10px] font-bold text-slate-655 uppercase"
                  >KONFIRMASI KATA SANDI</label
                >
                <input
                  v-model="editForm.password_confirmation"
                  type="password"
                  class="w-full px-3 py-2 text-xs border-2 border-slate-200 rounded focus:outline-none focus:border-retro-blue font-sans bg-white"
                  placeholder="Ulangi kata sandi baru..."
                />
              </div>
            </div>
          </div>

          <div class="flex gap-3 border-t border-slate-100 pt-6 font-sans">
            <button
              type="button"
              @click="lockProfile"
              class="flex-1 py-2 text-xs font-bold border-2 border-slate-200 text-slate-600 hover:bg-slate-50 rounded transition-colors uppercase text-center"
            >
              KUNCI & KEMBALI
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="flex-1 py-2 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 rounded transition-colors uppercase shadow-sm text-center"
            >
              {{ processing ? 'MEMPROSES...' : 'SIMPAN PERUBAHAN' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { profileService } from '@/services'
import { customDialog } from '@/utils/dialog'
import { toast } from '@/utils/toast'

const authStore = useAuthStore()

// State
const verified = ref(false)
const verificationToken = ref('')
const verificationMethod = ref<'password' | 'email'>('password')
const processing = ref(false)

// OTP flow state
const otpSent = ref(false)
const maskedEmail = ref('')

// User info cache
const profileInfo = ref({
  id: 0,
  name: '',
  username: '',
  email: '',
  role: '',
})

// Forms
const passwordForm = ref({
  password: '',
})

const otpForm = ref({
  code: '',
})

const editForm = ref({
  email: '',
  password: '',
  password_confirmation: '',
})

// Load basic profile info
async function loadProfile() {
  try {
    const res = await profileService.getProfile()
    const data = res.data
    profileInfo.value = data
    editForm.value.email = data.email || ''

    // Mask current email
    if (data.email) {
      const emailParts = data.email.split('@')
      maskedEmail.value =
        emailParts[0].substring(0, 2) +
        '*'.repeat(Math.max(0, emailParts[0].length - 2)) +
        '@' +
        emailParts[1]
    } else {
      maskedEmail.value = 'Tidak ada email terdaftar. Silakan gunakan metode Kata Sandi.'
    }
  } catch (err: any) {
    toast.error(err.response?.data?.message || 'Gagal memuat informasi profil.')
  }
}

onMounted(() => {
  loadProfile()
})

// Verify via Password
async function handlePasswordVerify() {
  if (processing.value) return
  processing.value = true
  try {
    const res = await profileService.verifyPassword(passwordForm.value.password)
    verificationToken.value = res.data.verification_token
    verified.value = true
    passwordForm.value.password = ''
    toast.success('Keamanan berhasil diverifikasi!')
  } catch (err: any) {
    customDialog.error(err.response?.data?.message || 'Gagal memverifikasi kata sandi.')
  } finally {
    processing.value = false
  }
}

// Send OTP code
async function handleSendOtp() {
  if (processing.value) return
  processing.value = true
  try {
    const res = await profileService.sendOtp()
    otpSent.value = true
    customDialog.success(res.data.message)
  } catch (err: any) {
    customDialog.error(err.response?.data?.message || 'Gagal mengirim OTP.')
  } finally {
    processing.value = false
  }
}

// Verify OTP code
async function handleOtpVerify() {
  if (processing.value) return
  processing.value = true
  try {
    const res = await profileService.verifyOtp(otpForm.value.code)
    verificationToken.value = res.data.verification_token
    verified.value = true
    otpForm.value.code = ''
    toast.success('Keamanan berhasil diverifikasi!')
  } catch (err: any) {
    customDialog.error(err.response?.data?.message || 'Kode OTP salah atau telah kedaluwarsa.')
  } finally {
    processing.value = false
  }
}

// Save Profile Updates
async function handleProfileUpdate() {
  if (processing.value) return

  // Validation
  if (editForm.value.password) {
    if (editForm.value.password.length < 6) {
      customDialog.warning('Kata sandi baru minimal harus 6 karakter.')
      return
    }
    if (editForm.value.password !== editForm.value.password_confirmation) {
      customDialog.warning('Konfirmasi kata sandi baru tidak cocok.')
      return
    }
  }

  processing.value = true
  try {
    const payload: { email?: string; password?: string; password_confirmation?: string } = {
      email: editForm.value.email,
    }
    if (editForm.value.password) {
      payload.password = editForm.value.password
      payload.password_confirmation = editForm.value.password_confirmation
    }

    await profileService.updateProfile(payload, verificationToken.value)
    customDialog.success('Perubahan profil berhasil disimpan.')

    // Refresh local cache and auth store if current email/name was updated
    await loadProfile()
    await authStore.fetchUser()

    // Reset passwords
    editForm.value.password = ''
    editForm.value.password_confirmation = ''

    // Lock again
    lockProfile()
  } catch (err: any) {
    customDialog.error(err.response?.data?.message || 'Gagal menyimpan perubahan profil.')
  } finally {
    processing.value = false
  }
}

// Lock profile editing again
function lockProfile() {
  verified.value = false
  verificationToken.value = ''
  otpSent.value = false
  editForm.value.password = ''
  editForm.value.password_confirmation = ''
}
</script>
