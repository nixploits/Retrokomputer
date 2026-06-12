<template>
  <button
    @click="toggleTheme"
    class="relative select-none flex items-center w-[60px] h-8 p-[3px] rounded-full cursor-pointer focus:outline-none transition-all duration-300 theme-switch-track"
    :class="isDark ? 'track-dark' : 'track-light'"
    role="switch"
    :aria-checked="isDark"
    title="Ganti Tema"
  >
    <!-- Background Track Icons -->
    <span
      class="absolute inset-0 flex justify-between items-center px-2 pointer-events-none select-none"
    >
      <!-- Sun Icon (left) -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="w-3.5 h-3.5 transition-colors duration-300"
        :class="isDark ? 'text-slate-600' : 'text-slate-400'"
      >
        <circle cx="12" cy="12" r="4" />
        <path d="M12 3v2" />
        <path d="M12 19v2" />
        <path d="M5 12H3" />
        <path d="M21 12h-2" />
        <path d="M18.36 5.64l-1.42 1.42" />
        <path d="M7.05 16.95l-1.42 1.42" />
        <path d="M18.36 18.36l-1.42-1.42" />
        <path d="M7.05 7.05L5.64 5.64" />
      </svg>

      <!-- Moon Icon (right) -->
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="w-3.5 h-3.5 transition-colors duration-300"
        :class="isDark ? 'text-slate-400' : 'text-slate-600'"
      >
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
      </svg>
    </span>

    <!-- Sliding Knob -->
    <span
      class="w-6 h-6 rounded-full flex items-center justify-center shadow-md transition-all duration-300 ease-out theme-switch-knob"
      :class="isDark ? 'knob-dark' : 'knob-light'"
    >
      <!-- Sun Icon inside knob (active in Light Mode) -->
      <svg
        v-if="!isDark"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="3"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="w-3.5 h-3.5 text-white"
      >
        <circle cx="12" cy="12" r="4" />
        <path d="M12 3v2" />
        <path d="M12 19v2" />
        <path d="M5 12H3" />
        <path d="M21 12h-2" />
        <path d="M18.36 5.64l-1.42 1.42" />
        <path d="M7.05 16.95l-1.42 1.42" />
        <path d="M18.36 18.36l-1.42-1.42" />
        <path d="M7.05 7.05L5.64 5.64" />
      </svg>

      <!-- Moon Icon inside knob (active in Dark Mode) -->
      <svg
        v-else
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="w-3.5 h-3.5 text-white"
      >
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
      </svg>
    </span>
  </button>
</template>

<script setup lang="ts">
import { useTheme } from '@/utils/theme'

const { isDark, toggleTheme } = useTheme()
</script>

<style scoped>
.theme-switch-track {
  box-sizing: border-box;
}

/* Light Mode Track - High Contrast */
.track-light {
  background-color: #ffffff;
  border: 1.5px solid #475569; /* Slate-600 border for crisp contrast on light bg */
}
.track-light:hover {
  border-color: #1d4ed8; /* Retro Blue hover border */
  box-shadow: 0 0 6px rgba(29, 78, 216, 0.15);
}

/* Dark Mode Track - High Contrast */
.track-dark {
  background-color: #05070c; /* Very deep dark black */
  border: 1.5px solid #475569; /* Slate-600 border for clean frame */
}
.track-dark:hover {
  border-color: #ff7a00; /* Retro Orange hover border */
  box-shadow: 0 0 6px rgba(255, 122, 0, 0.2);
}

/* Knob in Light Mode - High Contrast Blue */
.knob-light {
  background-color: #1d4ed8; /* Vibrant retro blue */
  transform: translateX(0);
  box-shadow:
    0 2px 4px rgba(29, 78, 216, 0.25),
    0 0 8px rgba(29, 78, 216, 0.35);
}

/* Knob in Dark Mode - High Contrast Orange */
.knob-dark {
  background-color: #ff7a00; /* Vibrant retro orange */
  transform: translateX(28px); /* 60px width - 6px padding - 26px knob = 28px */
  box-shadow:
    0 2px 4px rgba(255, 122, 0, 0.3),
    0 0 10px rgba(255, 122, 0, 0.6);
}

/* Interactions */
.theme-switch-track:active .theme-switch-knob {
  width: 28px; /* Squash effect on click */
}
.theme-switch-track:active .knob-dark {
  transform: translateX(26px); /* Adjust translation slightly during squash */
}
.theme-switch-knob {
  transition:
    transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
    width 0.2s ease,
    background-color 0.3s ease,
    box-shadow 0.3s ease;
}
</style>
