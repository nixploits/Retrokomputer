import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User, ProfilKasir } from '@/types'
import { authService, profilKasirService } from '@/services'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isOwner = computed(() => user.value?.role === 'owner')
  const isKasir = computed(() => user.value?.role === 'kasir')
  const userName = computed(() => user.value?.name || '')
  const activeKasirProfile = ref<ProfilKasir | null>(null)

  async function fetchActiveKasirProfile() {
    if (!isKasir.value) return
    try {
      const res = await profilKasirService.getAktif()
      activeKasirProfile.value = res.data
    } catch {
      activeKasirProfile.value = null
    }
  }

  function init() {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser = localStorage.getItem('auth_user')
    if (savedToken && savedUser) {
      token.value = savedToken
      try {
        user.value = JSON.parse(savedUser)
        if (user.value?.role === 'kasir') {
          fetchActiveKasirProfile()
        }
      } catch {
        logout()
      }
    }
  }

  async function login(username: string, password: string) {
    loading.value = true
    error.value = null
    try {
      const res = await authService.login({ username, password })
      const data = res.data
      token.value = data.access_token
      user.value = data.user
      localStorage.setItem('auth_token', data.access_token)
      localStorage.setItem('auth_user', JSON.stringify(data.user))

      if (data.user.role === 'owner') {
        router.push('/dashboard/owner')
      } else {
        if (data.user.role === 'kasir') {
          await fetchActiveKasirProfile()
        }
        router.push('/dashboard')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Login gagal. Periksa email dan password.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await authService.logout()
      }
    } catch {
      // ignore
    } finally {
      token.value = null
      user.value = null
      activeKasirProfile.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      router.push('/login')
    }
  }

  async function fetchUser() {
    try {
      const res = await authService.me()
      user.value = res.data
      localStorage.setItem('auth_user', JSON.stringify(res.data))
    } catch {
      logout()
    }
  }

  return {
    user,
    token,
    loading,
    error,
    activeKasirProfile,
    isAuthenticated,
    isAdmin,
    isOwner,
    isKasir,
    userName,
    init,
    login,
    logout,
    fetchUser,
    fetchActiveKasirProfile,
  }
})
