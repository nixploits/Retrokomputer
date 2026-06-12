<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
      <div>
        <h2 class="text-base font-semibold text-slate-800">Pengaturan Sistem</h2>
        <p class="text-xs text-slate-500">Konfigurasi tampilan dan informasi sistem.</p>
      </div>
      <div
        class="text-xs px-2.5 py-1 bg-retro-blue/10 text-retro-blue font-semibold rounded-full border border-retro-blue/20"
      >
        Admin Mode
      </div>
    </div>

    <!-- System Info Card -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 space-y-4 shadow-sm">
      <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
        <span class="text-retro-blue text-lg">■</span>
        <h3 class="text-sm font-semibold text-slate-700">Informasi Sistem</h3>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <div class="space-y-1">
          <span class="block font-bold text-slate-500 uppercase">Nama Aplikasi</span>
          <span class="font-bold text-slate-800">Retro Komputer POS</span>
        </div>
        <div class="space-y-1">
          <span class="block font-bold text-slate-500 uppercase">Versi</span>
          <span class="font-bold text-slate-800 font-mono">v1.0.0</span>
        </div>
        <div class="space-y-1">
          <span class="block font-bold text-slate-500 uppercase">Logo</span>
          <span class="font-bold text-slate-800">Logo Teks Pixel (Default)</span>
        </div>
        <div class="space-y-1">
          <span class="block font-bold text-slate-500 uppercase">Background Login</span>
          <span class="font-bold text-slate-800">Pola Retro Grid (Garis Kotak)</span>
        </div>
      </div>
    </div>

    <!-- Background Preview Card -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 space-y-4 shadow-sm">
      <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
        <span class="text-retro-orange text-lg">■</span>
        <h3 class="text-sm font-semibold text-slate-700">Preview Background Login</h3>
      </div>

      <div
        class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-lg border-2 border-retro-orange/30 p-4"
      >
        <p
          class="text-[10px] font-semibold uppercase tracking-wider text-retro-orange drop-shadow mb-3"
        >
          ■ Preview Tampilan Latar Login
        </p>
        <div
          class="h-32 rounded-lg border-2 border-retro-orange/40 flex items-center justify-center overflow-hidden relative shadow-lg"
          style="background-color: #111827"
        >
          <!-- Grid Effect Overlay -->
          <div class="absolute inset-0 pointer-events-none bg-grid" />

          <div class="z-10 text-center px-4">
            <p class="text-sm font-mono font-bold drop-shadow-lg text-white">
              Demo Latar Belakang Login
            </p>
            <p class="text-[10px] mt-1 drop-shadow text-slate-200">
              Tipe: <span class="font-bold text-retro-orange">RETRO GRID</span>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Logo Preview Card -->
    <div class="bg-white rounded-lg border border-slate-200 p-5 space-y-4 shadow-sm">
      <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
        <span class="text-retro-blue text-lg">■</span>
        <h3 class="text-sm font-semibold text-slate-700">Preview Logo</h3>
      </div>

      <div
        class="bg-gradient-to-br from-retro-dark to-retro-dark-card rounded-lg border-2 border-retro-orange/30 p-4"
      >
        <p
          class="text-[10px] font-semibold uppercase tracking-wider text-retro-orange drop-shadow mb-3"
        >
          ■ Preview Logo Retro
        </p>
        <div
          class="bg-retro-dark rounded-md border border-retro-orange/20 p-4 flex items-center justify-center min-h-24"
        >
          <div class="flex items-center gap-3">
            <div
              class="px-4 bg-retro-dark flex items-center justify-center rounded border-2 border-retro-orange/40 shadow-lg"
              style="height: 44px"
            >
              <span
                class="text-retro-orange font-mono font-bold tracking-widest drop-shadow-lg"
                style="font-size: 14.4px"
              >
                ▌Retro Komputer▐
              </span>
            </div>
            <div class="text-xs">
              <p class="text-retro-orange font-bold">Text Logo</p>
              <p class="text-slate-400 text-[10px]">Height: 32px</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Settings page simplified — no logo/background upload features
// Background always uses retro grid pattern, logo always uses default text pixel
</script>

<style scoped>
.bg-grid {
  background-size: 20px 20px;
  background-image:
    linear-gradient(to right, rgba(255, 122, 0, 0.1) 1px, transparent 1px),
    linear-gradient(to bottom, rgba(255, 122, 0, 0.1) 1px, transparent 1px);
}
</style>
