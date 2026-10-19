<script setup lang="ts">
import AppShell from '@/components/layout/AppShell.vue'
import AppToast from '@/components/ui/AppToast.vue'
</script>

<template>
  <v-app>
    <AppShell>
      <router-view />
    </AppShell>

    <AppToast />
  </v-app>
</template>

<style>
html,
body {
  background: #f4f6fa;
}

.v-application {
  font-family: 'Inter', 'Roboto', sans-serif;
}

/* O Vuetify deixa o texto dos botoes em caixa alta; os prototipos usam
   capitalizacao normal. */
.v-application .v-btn {
  text-transform: none;
  letter-spacing: normal;
  font-size: 15px;
  font-weight: 600;
}
</style>
