<template>
  <div
    class="min-h-screen flex items-center justify-center p-4 transition-all duration-300 relative"
    :class="isDark ? 'bg-[#111827]' : 'bg-slate-100'"
  >
    <!-- Grid Overlay -->
    <div class="absolute inset-0 pointer-events-none bg-grid z-0 animate-fadeIn" />

    <!-- Theme Toggle Switch (Fixed Top Right) -->
    <div class="fixed top-4 right-4 z-50 animate-fadeIn">
      <ThemeSwitch />
    </div>

    <div class="w-full max-w-sm animate-slideUp z-10">
      <!-- Text Custom Header with Logo -->
      <div class="text-center mb-8 flex flex-col items-center">
        <div
          class="w-16 h-16 mb-3 p-1 bg-slate-800 dark:bg-slate-800 rounded-lg border border-slate-700 dark:border-slate-700 shadow-md"
          :class="{ '!bg-slate-200 !border-slate-300': !isDark }"
        >
          <img src="/logo.svg" alt="Retro Komputer Logo" class="w-full h-full object-contain" />
        </div>
        <h1
          class="text-2xl font-extrabold font-display tracking-wide text-retro-orange drop-shadow-lg"
        >
          Retro Komputer
        </h1>
        <p
          class="drop-shadow text-xs mt-2 font-medium tracking-wide"
          :class="isDark ? 'text-slate-300' : 'text-slate-500'"
        >
          Sistem POS & Inventory
        </p>
      </div>

      <!-- Card Container -->
      <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-md">
        <!-- Profile/Person Icon -->
        <div class="flex justify-center mb-3">
          <div
            class="p-3 bg-slate-50 rounded-full border border-slate-100 text-slate-500 shadow-inner"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="2"
              stroke="currentColor"
              class="w-6 h-6"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
              />
            </svg>
          </div>
        </div>

        <h2 class="text-base font-bold text-retro-orange text-center mb-5">Masuk ke Sistem</h2>

        <div
          v-if="authStore.error"
          class="mb-4 p-2.5 rounded-md bg-red-50 border border-red-200 text-red-600 text-xs"
        >
          {{ authStore.error }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Username atau Gmail</label>
            <input
              id="login-username"
              v-model="username"
              type="text"
              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-retro-blue focus:border-retro-blue"
              placeholder="Masukkan username atau gmail"
              required
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Kata Sandi</label>
            <input
              id="login-password"
              v-model="password"
              type="password"
              class="w-full px-3 py-2 text-xs border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-retro-blue focus:border-retro-blue"
              placeholder="••••••••"
              required
            />
          </div>
          <button
            id="login-submit"
            type="submit"
            :disabled="authStore.loading"
            class="w-full py-2 text-xs font-bold text-white bg-retro-orange hover:bg-retro-orange-dark disabled:opacity-50 rounded-md transition-colors shadow-md hover:shadow-lg"
          >
            {{ authStore.loading ? 'Memproses...' : 'Masuk' }}
          </button>
        </form>
        <div class="mt-5 pt-3 border-t border-slate-100 text-center">
          <p class="text-slate-400 text-[10px] tracking-wider font-mono">v1.0</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useTheme } from '@/utils/theme'
import ThemeSwitch from '@/components/ThemeSwitch.vue'

const authStore = useAuthStore()
const { isDark, initTheme } = useTheme()
const username = ref('')
const password = ref('')

onMounted(() => {
  initTheme()
})

async function handleLogin() {
  try {
    await authStore.login(username.value, password.value)
  } catch {
    // handled by store
  }
}
</script>

<style scoped>
.bg-grid {
  background-size: 24px 24px;
  background-image:
    linear-gradient(to right, rgba(255, 122, 0, 0.1) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 122, 0, 0.1) 1px, transparent 1px);
}
</style>
