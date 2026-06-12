<template>
  <div class="flex h-screen bg-slate-50">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-all duration-300 ease-in-out lg:relative overflow-hidden',
        isMobile
          ? sidebarOpen
            ? 'w-56 translate-x-0'
            : 'w-56 -translate-x-full'
          : sidebarOpen
            ? 'w-56 translate-x-0'
            : 'w-16 translate-x-0',
      ]"
    >
      <!-- Logo & Header -->
      <div
        class="flex items-center border-b border-slate-200 dark:border-slate-800 shrink-0 transition-all duration-150 justify-between select-none h-14"
      >
        <div v-if="sidebarOpen" class="flex items-center justify-between w-full px-4">
          <div class="flex items-center gap-2.5">
            <!-- Logo Image -->
            <div class="w-8 h-8 shrink-0">
              <img src="/logo.svg" alt="Retro Komputer" class="w-full h-full object-contain" />
            </div>
            <div class="flex flex-col text-left">
              <span
                class="text-xs font-mono font-bold text-slate-800 dark:text-white tracking-wide uppercase leading-tight"
                >Retrokomputer</span
              >
              <span
                class="text-[9px] text-slate-500 dark:text-slate-400 font-sans mt-0.5 leading-none"
                >POS & Inventory</span
              >
            </div>
          </div>
          <!-- Close button on Mobile -->
          <button
            v-if="isMobile"
            @click="sidebarOpen = false"
            class="text-slate-400 hover:text-white transition-colors p-1"
            title="Tutup Menu"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div v-else class="flex items-center justify-center w-full">
          <!-- Collapsed Logo -->
          <div class="w-8 h-8 shrink-0">
            <img src="/logo.svg" alt="Retro Komputer" class="w-full h-full object-contain" />
          </div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-1">
        <template v-for="group in menuGroups" :key="group.label">
          <p
            :class="[
              'px-3.5 pt-4 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500 transition-all duration-300 ease-in-out truncate',
              sidebarOpen
                ? 'opacity-100 max-h-10'
                : 'opacity-0 max-h-0 py-0 overflow-hidden pointer-events-none',
            ]"
          >
            {{ group.label }}
          </p>
          <router-link
            v-for="item in group.items"
            :key="item.path"
            :to="item.path"
            :class="[
              'flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-[13px] transition-all sidebar-link',
              isActive(item.path)
                ? 'bg-retro-primary text-white font-bold shadow-md shadow-retro-primary/20'
                : 'text-slate-400 hover:bg-slate-800/40 hover:text-white',
            ]"
            @click="isMobile && (sidebarOpen = false)"
          >
            <span
              class="shrink-0 w-5 h-5 flex items-center justify-center"
              v-html="getIconSvg(item.icon)"
            ></span>
            <span
              :class="[
                'transition-all duration-300 ease-in-out truncate',
                sidebarOpen
                  ? 'opacity-100 max-w-[120px] ml-0'
                  : 'opacity-0 max-w-0 ml-0 overflow-hidden pointer-events-none',
              ]"
            >
              {{ item.label }}
            </span>
          </router-link>
        </template>
      </nav>

      <!-- User Info -->
      <div
        :class="[
          'border-t border-slate-200 dark:border-slate-800 shrink-0 transition-all duration-300 ease-in-out h-14 flex items-center px-4',
          sidebarOpen ? 'opacity-100' : 'opacity-0 overflow-hidden pointer-events-none',
        ]"
      >
        <div class="flex items-center gap-2.5 w-full">
          <div
            class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0"
          >
            <span class="text-slate-600 dark:text-slate-300 text-xs font-semibold">{{
              userInitial
            }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate">
              {{ authStore.userName }}
            </p>
            <p class="text-[10px] text-slate-400 uppercase font-semibold font-mono">
              {{ authStore.user?.role }}
              <span
                v-if="authStore.activeKasirProfile?.nama"
                class="text-retro-primary font-bold font-sans lowercase text-[9px] block"
              >
                kasir: {{ authStore.activeKasirProfile.nama }}
              </span>
            </p>
          </div>
          <button
            @click="triggerLogout"
            class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 text-slate-400 hover:text-red-500 transition-colors"
            title="Logout"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-4 h-4"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M3 3a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h5a1 1 0 1 0 0-2H4V5h4a1 1 0 1 0 0-2H3zm12.293 3.293a1 1 0 0 1 1.414 0l3 3a1 1 0 0 1 0 1.414l-3 3a1 1 0 0 1-1.414-1.414L16.586 11H8a1 1 0 1 1 0-2h8.586l-1.293-1.293a1 1 0 0 1 0-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen && isMobile"
      class="fixed inset-0 bg-black/20 z-40 lg:hidden"
      @click="sidebarOpen = false"
    />

    <!-- Main -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <header
        class="h-14 flex items-center justify-between px-4 lg:px-6 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shrink-0"
      >
        <div class="flex items-center gap-3">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="p-1.5 rounded-md hover:bg-slate-100 text-slate-500 transition-colors"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M3 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1zm0 5a1 1 0 0 1 1-1h12a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1zm0 5a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
          <h2 class="text-sm font-semibold text-slate-800 font-sans">{{ pageTitle }}</h2>
        </div>

        <div class="flex items-center gap-4">
          <!-- POS Button (Kasir only) -->
          <router-link
            v-if="authStore.isKasir"
            to="/pos"
            class="text-xs font-bold px-3 py-1.5 bg-retro-primary hover:bg-retro-primary-hover text-white rounded transition-colors hidden sm:inline-flex items-center gap-1.5"
          >
            <span>⊞</span> Kasir POS
          </router-link>

          <!-- Theme Toggle Switch -->
          <ThemeSwitch />

          <!-- Profile Dropdown -->
          <div class="relative profile-dropdown-container">
            <button
              @click="toggleDropdown"
              class="flex items-center gap-2 p-1 rounded-md hover:bg-slate-50 border border-slate-100 transition-colors"
            >
              <div class="w-8 h-8 rounded bg-retro-primary/10 flex items-center justify-center">
                <span class="text-retro-primary font-bold text-xs font-mono">{{
                  userInitial
                }}</span>
              </div>
              <div class="hidden md:flex flex-col text-left shrink-0 max-w-[120px]">
                <span class="text-xs font-semibold text-slate-700 truncate leading-tight">{{
                  authStore.userName
                }}</span>
                <span class="text-[9px] font-bold font-mono text-slate-400 uppercase leading-none">
                  {{ authStore.user?.role }}
                  <span
                    v-if="authStore.activeKasirProfile?.nama"
                    class="text-retro-primary font-bold"
                  >
                    ({{ authStore.activeKasirProfile.nama }})
                  </span>
                </span>
              </div>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-3 h-3 text-slate-400"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <transition name="dropdown">
              <div
                v-if="dropdownOpen"
                class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-md shadow-lg py-1.5 z-50 animate-fadeIn font-sans"
              >
                <div class="px-3 py-2 border-b border-slate-100">
                  <p class="text-xs font-semibold text-slate-800 truncate">
                    {{ authStore.userName }}
                  </p>
                  <p class="text-[10px] text-slate-400 font-mono font-bold uppercase mt-0.5">
                    {{ authStore.user?.role }}
                  </p>
                </div>
                <div
                  class="py-1 border-b border-slate-100"
                  v-if="authStore.isAdmin || authStore.isOwner"
                >
                  <router-link
                    to="/profil"
                    @click="closeDropdown"
                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50 text-left transition-colors font-medium"
                  >
                    <span>⚙️</span>
                    <span>Profil Saya</span>
                  </router-link>
                </div>
                <div class="py-1">
                  <button
                    @click="triggerLogout"
                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50 text-left transition-colors"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M3 3a1 1 0 00-1 1v12a1 1 0 00 1 1h5a1 1 0 100-2H4V5h4a1 1 0 100-2H3zm12.293 3.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L16.586 11H8a1 1 0 110-2h8.586l-1.293-1.293a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    <span>Keluar Akun</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 overflow-y-auto p-4 lg:p-6">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>

    <!-- Confirmation Modal -->
    <div
      v-if="showLogoutConfirm"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-retro-dark/60 backdrop-blur-sm animate-fadeIn"
    >
      <div
        class="bg-white border-2 border-retro-primary rounded-lg max-w-sm w-full mx-4 overflow-hidden shadow-2xl font-mono"
      >
        <!-- Title bar -->
        <div class="bg-retro-primary text-white px-4 py-2 flex items-center justify-between">
          <span class="font-bold text-xs font-mono">KONFIRMASI KELUAR</span>
          <button
            @click="showLogoutConfirm = false"
            class="text-white hover:text-retro-accent transition-colors font-bold text-lg leading-none"
          >
            ×
          </button>
        </div>
        <div class="p-6">
          <div class="flex items-start gap-3">
            <div
              class="w-10 h-10 rounded bg-amber-50 border border-amber-300 flex items-center justify-center shrink-0 text-amber-500 text-xl font-bold"
            >
              !
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800 mb-1">Keluar dari Aplikasi?</h3>
              <p class="text-xs text-slate-500 leading-relaxed font-sans">
                Apakah Anda yakin ingin keluar dari sistem Retrokomputer? Sesi Anda akan diakhiri.
              </p>
            </div>
          </div>
        </div>
        <div class="bg-slate-50 px-4 py-3 flex justify-end gap-2 border-t border-slate-100">
          <button
            @click="showLogoutConfirm = false"
            class="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded font-sans transition-colors"
          >
            Batal
          </button>
          <button
            @click="handleLogout"
            class="px-3 py-1.5 text-xs bg-red-600 hover:bg-red-700 text-white rounded font-sans font-semibold transition-colors shadow-sm"
          >
            Ya, Keluar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ThemeSwitch from '@/components/ThemeSwitch.vue'

const route = useRoute()
const authStore = useAuthStore()

const sidebarOpen = ref(true)
const isMobile = ref(false)

const dropdownOpen = ref(false)
const showLogoutConfirm = ref(false)

const userInitial = computed(() => {
  const name = authStore.userName
  return name ? name.charAt(0).toUpperCase() : '?'
})

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    dashboard: 'Dashboard',
    'dashboard-owner': 'Dashboard Owner',
    produk: 'Produk',
    'produk-tambah': 'Tambah Produk',
    'produk-edit': 'Edit Produk',
    pos: 'Kasir POS',
    transaksi: 'Transaksi',
    'transaksi-detail': 'Detail Transaksi',
    pembelian: 'Pembelian',
    'pembelian-tambah': 'Tambah Pembelian',
    supplier: 'Supplier',
    retur: 'Retur',
    'retur-tambah': 'Tambah Retur',
    'stok-riwayat': 'Riwayat Stok',
    'laporan-penjualan': 'Laporan Penjualan',
    'laporan-stok': 'Laporan Stok',
    'laporan-laba-rugi': 'Laba Kotor',
    'barang-rusak': 'Barang Rusak / Hilang',
    'profil-kasir': 'Profil Kasir',
    profil: 'Profil Saya',
    settings: 'Pengaturan Sistem',
  }
  return titles[route.name as string] || 'Retro Komputer'
})

interface MenuItem {
  label: string
  icon: string
  path: string
}
interface MenuGroup {
  label: string
  items: MenuItem[]
}

const menuGroups = computed<MenuGroup[]>(() => {
  if (authStore.isOwner) {
    return [
      {
        label: 'Overview',
        items: [{ label: 'Dashboard', icon: 'dashboard', path: '/dashboard/owner' }],
      },
      {
        label: 'Monitoring',
        items: [
          { label: 'Transaksi', icon: 'transaksi', path: '/transaksi' },
          { label: 'Barang Rusak', icon: 'barang-rusak', path: '/barang-rusak' },
          { label: 'Riwayat Stok', icon: 'stok-riwayat', path: '/stok/riwayat' },
        ],
      },
      {
        label: 'Laporan',
        items: [
          { label: 'Penjualan', icon: 'penjualan', path: '/laporan/penjualan' },
          { label: 'Stok', icon: 'stok', path: '/laporan/stok' },
          { label: 'Laba Kotor', icon: 'laba-rugi', path: '/laporan/laba-rugi' },
        ],
      },
    ]
  }

  // Kasir: only show their relevant menu items
  if (authStore.isKasir) {
    return [
      {
        label: 'Menu',
        items: [
          { label: 'Dashboard', icon: 'dashboard', path: '/dashboard' },
          { label: 'Profil Kasir', icon: 'profil-kasir', path: '/profil-kasir' },
          { label: 'Kasir POS', icon: 'pos', path: '/pos' },
        ],
      },
      {
        label: 'Laporan',
        items: [
          { label: 'Transaksi', icon: 'transaksi', path: '/transaksi' },
          { label: 'Penjualan', icon: 'penjualan', path: '/laporan/penjualan' },
        ],
      },
    ]
  }

  // Admin items
  const items = [
    { label: 'Menu', items: [{ label: 'Dashboard', icon: 'dashboard', path: '/dashboard' }] },
    {
      label: 'Inventaris',
      items: [
        { label: 'Produk', icon: 'produk', path: '/produk' },
        { label: 'Supplier', icon: 'supplier', path: '/supplier' },
        { label: 'Pembelian', icon: 'pembelian', path: '/pembelian' },
        { label: 'Retur', icon: 'retur', path: '/retur' },
        { label: 'Barang Rusak', icon: 'barang-rusak', path: '/barang-rusak' },
      ],
    },
    {
      label: 'Stok & Laporan',
      items: [
        { label: 'Riwayat Stok', icon: 'stok-riwayat', path: '/stok/riwayat' },
        { label: 'Transaksi', icon: 'transaksi', path: '/transaksi' },
        { label: 'Penjualan', icon: 'penjualan', path: '/laporan/penjualan' },
        { label: 'Stok', icon: 'stok', path: '/laporan/stok' },
      ],
    },
    {
      label: 'Pengelolaan',
      items: [{ label: 'Profil Kasir', icon: 'profil-kasir', path: '/profil-kasir' }],
    },
  ]

  // (Removed system administration menu)

  return items
})

function isActive(path: string) {
  return route.path === path || route.path.startsWith(path + '/')
}

function checkMobile() {
  isMobile.value = window.innerWidth < 1024
  if (isMobile.value) sidebarOpen.value = false
}

function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value
}

function closeDropdown() {
  dropdownOpen.value = false
}

function triggerLogout() {
  closeDropdown()
  showLogoutConfirm.value = true
}

function handleLogout() {
  showLogoutConfirm.value = false
  authStore.logout()
}

function handleWindowClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (dropdownOpen.value && !target.closest('.profile-dropdown-container')) {
    closeDropdown()
  }
}

function getIconSvg(iconName: string): string {
  const icons: Record<string, string> = {
    dashboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>`,
    transaksi: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>`,
    'barang-rusak': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line><line x1="9" y1="14" x2="15" y2="10"></line><line x1="15" y1="14" x2="9" y2="10"></line></svg>`,
    'stok-riwayat': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>`,
    penjualan: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>`,
    stok: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>`,
    'laba-rugi': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>`,
    'profil-kasir': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>`,
    pos: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>`,
    produk: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>`,
    supplier: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>`,
    pembelian: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>`,
    retur: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4.5 h-4.5"><path d="M3 2v6h6M3 10a9 9 0 1 1 3.4-6.9l-3.4 3.4"></path></svg>`,
  }
  return (
    icons[iconName] ||
    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4.5 h-4.5"><circle cx="12" cy="12" r="10"></circle></svg>`
  )
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
  window.addEventListener('click', handleWindowClick)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  window.removeEventListener('click', handleWindowClick)
})
</script>

<style scoped>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.15s ease;
}
.page-enter-from,
.page-leave-to {
  opacity: 0;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.sidebar-link {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.sidebar-link.router-link-active {
  background-color: var(--color-primary, #ff7a00) !important;
  color: #ffffff !important;
  border-left: none !important;
  font-weight: 700 !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 12px rgba(255, 122, 0, 0.25) !important;
}

.sidebar-link :deep(svg) {
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-link:hover :deep(svg) {
  transform: scale(1.1) translateX(2px);
  color: var(--color-accent, #1d4ed8) !important;
}
</style>
