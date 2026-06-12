import { ref } from 'vue'

const isDark = ref(localStorage.getItem('theme') !== 'light')

export function useTheme() {
  const toggleTheme = () => {
    isDark.value = !isDark.value
    if (isDark.value) {
      document.documentElement.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    }
  }

  const initTheme = () => {
    // Check local storage, default to dark if not set
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme === 'light') {
      isDark.value = false
      document.documentElement.classList.remove('dark')
    } else {
      isDark.value = true
      document.documentElement.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    }
  }

  return {
    isDark,
    toggleTheme,
    initTheme,
  }
}
