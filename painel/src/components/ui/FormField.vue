<script setup lang="ts">
defineProps<{
  label: string
  inputId: string
  optional?: boolean
  error?: string
}>()
</script>

<template>
  <div class="field">
    <label class="field__label" :for="inputId">
      {{ label }}
      <span v-if="optional" class="field__optional">(Opcional)</span>
    </label>

    <slot />

    <p v-if="error" class="field__error">{{ error }}</p>
  </div>
</template>

<style scoped>
.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}

.field__label {
  color: #0f1b4c;
  font-size: 15px;
  font-weight: 600;
}

.field__optional {
  color: #8c97b2;
  font-size: 13px;
  font-weight: 400;
}

.field__error {
  margin: 0;
  color: #f5222d;
  font-size: 13px;
}
</style>
