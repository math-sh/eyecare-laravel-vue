<script setup lang="ts">
withDefaults(
  defineProps<{
    value: boolean
    title: string
    confirmLabel?: string
    cancelLabel?: string
    loading?: boolean
  }>(),
  { confirmLabel: 'Confirmar', cancelLabel: 'Cancelar', loading: false },
)

const emit = defineEmits<{
  (event: 'input', value: boolean): void
  (event: 'confirm'): void
}>()

function close(): void {
  emit('input', false)
}
</script>

<template>
  <v-dialog :value="value" max-width="448" @input="emit('input', $event)">
    <div class="confirm">
      <header class="confirm__header">
        <h2 class="confirm__title">{{ title }}</h2>
        <button class="confirm__close" type="button" aria-label="Fechar" @click="close">
          <v-icon size="22" color="#2F54EB">mdi-close</v-icon>
        </button>
      </header>

      <div class="confirm__body">
        <slot />
      </div>

      <footer class="confirm__footer">
        <button class="confirm__cancel" type="button" :disabled="loading" @click="close">
          {{ cancelLabel }}
        </button>

        <v-btn color="primary" depressed :loading="loading" @click="emit('confirm')">
          {{ confirmLabel }}
        </v-btn>
      </footer>
    </div>
  </v-dialog>
</template>

<style scoped>
.confirm {
  background: #fff;
  border-radius: 8px;
}

.confirm__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e8ecf4;
}

.confirm__title {
  margin: 0;
  color: #0f1b4c;
  font-size: 18px;
  font-weight: 500;
}

.confirm__close {
  background: none;
  border: 0;
  cursor: pointer;
  line-height: 0;
}

.confirm__body {
  padding: 20px 24px;
  color: #45527a;
  font-size: 15px;
  line-height: 1.5;
}

.confirm__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px 20px;
  border-top: 1px solid #e8ecf4;
}

.confirm__cancel {
  background: none;
  border: 0;
  color: #f5222d;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
}

.confirm__cancel:disabled {
  opacity: 0.5;
  cursor: default;
}
</style>
