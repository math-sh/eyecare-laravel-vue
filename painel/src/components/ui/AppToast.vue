<script setup lang="ts">
import { useToast } from '@/hooks/use-toast'

const { toasts, dismiss } = useToast()
</script>

<template>
  <div class="toast-stack">
    <transition-group name="toast">
      <div v-for="toast in toasts" :key="toast.id" class="toast" :class="`toast--${toast.kind}`">
        <span class="toast__icon">
          <v-icon size="18" color="white">
            {{ toast.kind === 'success' ? 'mdi-check' : 'mdi-close' }}
          </v-icon>
        </span>

        <p class="toast__message">{{ toast.message }}</p>

        <button class="toast__close" type="button" aria-label="Fechar" @click="dismiss(toast.id)">
          <v-icon size="20" color="#8C97B2">mdi-close</v-icon>
        </button>

        <span class="toast__progress" :style="{ animationDuration: `${toast.timeout}ms` }" />
      </div>
    </transition-group>
  </div>
</template>

<style scoped>
.toast-stack {
  position: fixed;
  top: 16px;
  right: 24px;
  z-index: 300;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.toast {
  position: relative;
  display: flex;
  align-items: center;
  gap: 12px;
  width: 324px;
  padding: 16px;
  overflow: hidden;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(15, 27, 76, 0.16);
}

.toast__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 auto;
  width: 32px;
  height: 32px;
  border-radius: 8px;
}

.toast--success .toast__icon {
  background: #22c55e;
}

.toast--error .toast__icon {
  background: #f5222d;
}

.toast__message {
  flex: 1;
  margin: 0;
  color: #0f1b4c;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.35;
}

.toast__close {
  flex: 0 0 auto;
  background: none;
  border: 0;
  cursor: pointer;
  line-height: 0;
}

.toast__progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 4px;
  border-radius: 0 4px 4px 0;
  animation-name: toast-progress;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}

.toast--success .toast__progress {
  background: #22c55e;
}

.toast--error .toast__progress {
  background: #f5222d;
}

@keyframes toast-progress {
  from {
    width: 100%;
  }
  to {
    width: 0;
  }
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}

.toast-enter,
.toast-leave-to {
  opacity: 0;
  transform: translateX(16px);
}
</style>
