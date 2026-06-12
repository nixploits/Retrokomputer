<template>
  <router-view />

  <!-- Global Toasts Container -->
  <div class="toast-container">
    <transition-group name="toast">
      <div v-for="t in toast.toasts.value" :key="t.id" class="toast-item" :class="t.type">
        <span class="toast-icon">
          <svg
            v-if="t.type === 'success'"
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
          </svg>
          <svg
            v-else-if="t.type === 'error'"
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
            />
          </svg>
          <svg
            v-else-if="t.type === 'warning'"
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
            />
          </svg>
          <svg
            v-else
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M11.25 11.25l.041-.02a.75.75 0 111.085 1.085l-.04.04m-2.137.882a.5.5 0 00.582.896l2.07-.93a1.5 1.5 0 00.9-.4l.08-.08"
            />
          </svg>
        </span>
        <span class="toast-message">{{ t.message }}</span>
        <button class="toast-close" @click="toast.dismiss(t.id)">×</button>
      </div>
    </transition-group>
  </div>

  <!-- Global Custom Dialog Modal -->
  <transition name="dialog-fade">
    <div
      v-if="customDialog.activeDialog.value"
      class="dialog-overlay"
      @click.self="customDialog.activeDialog.value.onCancel?.()"
    >
      <div class="dialog-box animate-scaleUp" :class="customDialog.activeDialog.value.type">
        <!-- Title bar -->
        <div class="dialog-header">
          <div class="flex items-center gap-1.5">
            <span class="dialog-title-prefix">■</span>
            <span class="dialog-title">{{ customDialog.activeDialog.value.title }}</span>
          </div>
          <button @click="customDialog.activeDialog.value.onCancel?.()" class="dialog-close-btn">
            ×
          </button>
        </div>

        <!-- Content Body -->
        <div class="dialog-body font-sans">
          <div class="dialog-icon-wrapper">
            <!-- Success Icon -->
            <div
              v-if="customDialog.activeDialog.value.type === 'success'"
              class="dialog-icon success"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>
            <!-- Error Icon -->
            <div
              v-else-if="customDialog.activeDialog.value.type === 'error'"
              class="dialog-icon error"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
                />
              </svg>
            </div>
            <!-- Warning / Confirm Icon -->
            <div
              v-else-if="
                customDialog.activeDialog.value.type === 'warning' ||
                customDialog.activeDialog.value.type === 'confirm'
              "
              class="dialog-icon warning"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                />
              </svg>
            </div>
            <!-- Info Icon -->
            <div v-else class="dialog-icon info">
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M11.25 11.25l.041-.02a.75.75 0 111.085 1.085l-.04.04m-2.137.882a.5.5 0 00.582.896l2.07-.93a1.5 1.5 0 00.9-.4l.08-.08"
                />
              </svg>
            </div>
          </div>

          <div class="dialog-message-content">
            <p class="dialog-message">{{ customDialog.activeDialog.value.message }}</p>
          </div>
        </div>

        <!-- Footer Actions -->
        <div class="dialog-footer">
          <button
            v-if="customDialog.activeDialog.value.cancelText"
            @click="customDialog.activeDialog.value.onCancel?.()"
            class="dialog-btn cancel"
          >
            {{ customDialog.activeDialog.value.cancelText }}
          </button>
          <button
            @click="customDialog.activeDialog.value.onConfirm?.()"
            class="dialog-btn confirm"
            :class="customDialog.activeDialog.value.type"
          >
            {{ customDialog.activeDialog.value.confirmText || 'Oke' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { toast } from '@/utils/toast'
import { customDialog } from '@/utils/dialog'
import { watch } from 'vue'

watch(
  () => customDialog.activeDialog.value,
  (newVal) => {
    if (newVal) {
      document.addEventListener('keydown', handleKeyDown)
    } else {
      document.removeEventListener('keydown', handleKeyDown)
    }
  },
)

function handleKeyDown(e: KeyboardEvent) {
  if (!customDialog.activeDialog.value) return
  if (e.key === 'Enter') {
    e.preventDefault()
    customDialog.activeDialog.value.onConfirm?.()
  } else if (e.key === 'Escape') {
    e.preventDefault()
    customDialog.activeDialog.value.onCancel?.()
  }
}
</script>

<style>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-width: 360px;
  width: calc(100% - 40px);
  pointer-events: none;
}

.toast-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background: linear-gradient(135deg, #131926 0%, #0e1320 100%) !important;
  border: 1px solid rgba(255, 122, 0, 0.2) !important;
  border-left: 4px solid #3b82f6 !important;
  border-radius: 8px;
  box-shadow:
    0 10px 15px -3px rgba(0, 0, 0, 0.4),
    0 0 15px rgba(255, 122, 0, 0.05);
  color: #f8fafc;
  pointer-events: auto;
  transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast-item.success {
  border-left-color: #10b981 !important;
  box-shadow:
    0 10px 15px -3px rgba(0, 0, 0, 0.4),
    0 0 15px rgba(16, 185, 129, 0.1);
}

.toast-item.error {
  border-left-color: #ef4444 !important;
  box-shadow:
    0 10px 15px -3px rgba(0, 0, 0, 0.4),
    0 0 15px rgba(239, 68, 68, 0.1);
}

.toast-item.warning {
  border-left-color: #f59e0b !important;
  box-shadow:
    0 10px 15px -3px rgba(0, 0, 0, 0.4),
    0 0 15px rgba(245, 158, 11, 0.1);
}

.toast-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-item.success .toast-icon {
  color: #10b981;
}
.toast-item.error .toast-icon {
  color: #ef4444;
}
.toast-item.warning .toast-icon {
  color: #f59e0b;
}
.toast-item.info .toast-icon {
  color: #3b82f6;
}

.toast-message {
  font-family: 'Inter', system-ui, sans-serif;
  font-size: 13px;
  font-weight: 500;
  flex-grow: 1;
  line-height: 1.4;
}

.toast-close {
  background: transparent !important;
  border: none !important;
  color: #64748b;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  padding: 0 4px;
  line-height: 1;
  transition: color 0.15s;
}

.toast-close:hover {
  color: #f8fafc;
}

/* Transitions */
.toast-enter-from {
  opacity: 0;
  transform: translateX(120px) scale(0.9);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(120px) scale(0.9);
}

/* Global Custom Dialog Modal Styling */
.dialog-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(4, 6, 10, 0.75) !important;
  backdrop-filter: blur(10px) !important;
  -webkit-backdrop-filter: blur(10px) !important;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.dialog-box {
  background: linear-gradient(145deg, var(--bg-card) 0%, var(--bg-deep) 100%) !important;
  border-radius: 12px !important;
  max-width: 420px;
  width: 100%;
  overflow: hidden !important;
  font-family: monospace;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
  border: 1px solid var(--color-accent) !important;
  transition: all 0.25s ease;
}

/* Borders based on type */
.dialog-box.success {
  border-color: var(--color-success) !important;
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.6),
    0 0 25px var(--color-success-glow) !important;
}
.dialog-box.error {
  border-color: var(--color-danger) !important;
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.6),
    0 0 25px var(--color-danger-glow) !important;
}
.dialog-box.warning,
.dialog-box.confirm {
  border-color: var(--color-accent) !important;
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.6),
    0 0 25px var(--color-accent-glow) !important;
}
.dialog-box.info {
  border-color: var(--color-primary) !important;
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.6),
    0 0 25px var(--color-primary-glow) !important;
}

/* Header */
.dialog-header {
  padding: 12px 18px !important;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dialog-box.success .dialog-header {
  background: linear-gradient(90deg, #064e3b 0%, var(--color-success) 100%) !important;
  border-bottom: 2px solid rgba(16, 185, 129, 0.2) !important;
}
.dialog-box.error .dialog-header {
  background: linear-gradient(90deg, #7f1d1d 0%, var(--color-danger) 100%) !important;
  border-bottom: 2px solid rgba(239, 68, 68, 0.2) !important;
}
.dialog-box.warning .dialog-header,
.dialog-box.confirm .dialog-header {
  background: linear-gradient(90deg, #78350f 0%, var(--color-accent) 100%) !important;
  border-bottom: 2px solid rgba(245, 158, 11, 0.2) !important;
}
.dialog-box.info .dialog-header {
  background: linear-gradient(90deg, #1e3a8a 0%, var(--color-primary) 100%) !important;
  border-bottom: 2px solid rgba(59, 130, 246, 0.2) !important;
}

.dialog-title-prefix {
  font-weight: bold;
  margin-right: 6px;
  color: #fff;
}
.dialog-title {
  font-weight: bold;
  text-transform: uppercase;
  font-size: 13px;
  color: #fff;
  letter-spacing: 0.05em;
}
.dialog-close-btn {
  background: transparent !important;
  border: none !important;
  color: rgba(255, 255, 255, 0.7);
  font-size: 20px;
  font-weight: bold;
  cursor: pointer;
  line-height: 1;
  transition: color 0.15s;
}
.dialog-close-btn:hover {
  color: #fff;
}

/* Body */
.dialog-body {
  padding: 24px 20px !important;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  background-color: transparent;
}
.dialog-icon-wrapper {
  flex-shrink: 0;
}
.dialog-icon {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.dialog-icon.success {
  background-color: var(--color-success-glow);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: var(--color-success);
}
.dialog-icon.error {
  background-color: var(--color-danger-glow);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: var(--color-danger);
}
.dialog-icon.warning {
  background-color: var(--color-accent-glow);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: var(--color-accent);
}
.dialog-icon.info {
  background-color: var(--color-primary-glow);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: var(--color-primary);
}

.dialog-message-content {
  flex-grow: 1;
}
.dialog-message {
  color: #e2e8f0;
  font-size: 13px;
  line-height: 1.6;
  font-weight: 500;
}

/* Footer */
.dialog-footer {
  padding: 12px 18px !important;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background-color: #090d16;
  border-top: 1px solid var(--border-color);
}

.dialog-btn {
  font-family: inherit;
  font-size: 12px;
  font-weight: bold;
  padding: 8px 18px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  text-transform: uppercase;
}

.dialog-btn.cancel {
  background-color: #1e293b !important;
  color: #94a3b8 !important;
  border: 1px solid #334155 !important;
}
.dialog-btn.cancel:hover {
  background-color: #334155 !important;
  color: #f8fafc !important;
}

.dialog-btn.confirm {
  color: #ffffff !important;
  border: 1px solid transparent !important;
}
.dialog-btn.confirm.success {
  background-color: var(--color-success) !important;
  box-shadow: 0 4px 6px var(--color-success-glow);
}
.dialog-btn.confirm.success:hover {
  background-color: var(--color-success-hover) !important;
  transform: translateY(-1px);
}
.dialog-btn.confirm.error {
  background-color: var(--color-danger) !important;
  box-shadow: 0 4px 6px var(--color-danger-glow);
}
.dialog-btn.confirm.error:hover {
  background-color: var(--color-danger-hover) !important;
  transform: translateY(-1px);
}
.dialog-btn.confirm.warning,
.dialog-btn.confirm.confirm {
  background-color: var(--color-accent) !important;
  box-shadow: 0 4px 6px var(--color-accent-glow);
}
.dialog-btn.confirm.warning:hover,
.dialog-btn.confirm.confirm:hover {
  background-color: var(--color-accent-hover) !important;
  transform: translateY(-1px);
}
.dialog-btn.confirm.info {
  background-color: var(--color-primary) !important;
  box-shadow: 0 4px 6px var(--color-primary-glow);
}
.dialog-btn.confirm.info:hover {
  background-color: var(--color-primary-hover) !important;
  transform: translateY(-1px);
}

.dialog-btn:active {
  transform: translateY(1px);
}

/* Animations */
@keyframes scaleUp {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
.animate-scaleUp {
  animation: scaleUp 0.18s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.dialog-fade-enter-active,
.dialog-fade-leave-active {
  transition: opacity 0.2s ease;
}
.dialog-fade-enter-from,
.dialog-fade-leave-to {
  opacity: 0;
}
</style>
