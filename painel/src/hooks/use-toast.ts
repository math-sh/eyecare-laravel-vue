import { ref } from 'vue'

export type ToastKind = 'success' | 'error'

export interface Toast {
  id: number
  kind: ToastKind
  message: string
  timeout: number
}

// Estado no modulo, nao no componente: qualquer tela empilha um toast e o
// AppToast, montado uma unica vez no App.vue, renderiza todos.
const toasts = ref<Toast[]>([])
let nextId = 0

function push(kind: ToastKind, message: string, timeout = 4000): void {
  const toast: Toast = { id: ++nextId, kind, message, timeout }

  toasts.value = [...toasts.value, toast]

  window.setTimeout(() => dismiss(toast.id), timeout)
}

function dismiss(id: number): void {
  toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

export function useToast() {
  return {
    toasts,
    dismiss,
    success: (message: string) => push('success', message),
    error: (message: string) => push('error', message),
  }
}
