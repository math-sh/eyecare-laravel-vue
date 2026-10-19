<script setup lang="ts">
import type { Location } from 'vue-router'

export interface Crumb {
  label: string
  to?: Location
}

defineProps<{ items: Crumb[] }>()
</script>

<template>
  <nav class="breadcrumb" aria-label="Você está em">
    <template v-for="(item, index) in items">
      <router-link v-if="item.to" :key="item.label" :to="item.to" class="breadcrumb__link">
        {{ item.label }}
      </router-link>
      <span v-else :key="item.label" class="breadcrumb__current">{{ item.label }}</span>

      <v-icon v-if="index < items.length - 1" :key="`sep-${index}`" size="18" color="#8C97B2">
        mdi-chevron-right
      </v-icon>
    </template>
  </nav>
</template>

<style scoped>
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
  font-size: 15px;
}

.breadcrumb__link {
  color: #2f54eb;
  font-weight: 500;
  text-decoration: none;
}

.breadcrumb__link:hover {
  text-decoration: underline;
}

.breadcrumb__current {
  color: #0f1b4c;
  font-weight: 500;
}
</style>
