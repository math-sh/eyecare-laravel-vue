<script setup lang="ts">
import { computed, getCurrentInstance, ref, watch } from 'vue'
import type { Location } from 'vue-router'

import logo from '../../assets/images/eyecare.jpg'

interface MenuLeaf {
  label: string
  icon: string
  to: Location
}

interface MenuGroup {
  label: string
  icon: string
  children: MenuLeaf[]
}

/** Estrutura do menu em array — basta adicionar grupos/itens aqui. */
const menu: MenuGroup[] = [
  {
    label: 'Atendimento',
    icon: 'mdi-stethoscope',
    children: [
      { label: 'Paciente', icon: 'mdi-account-group-outline', to: { name: 'patients.list' } },
    ],
  },
]

/** Itens navegáveis achatados, usados no modo colapsado (só ícones). */
const leaves = computed<MenuLeaf[]>(() => menu.flatMap((group) => group.children))

const COLLAPSE_KEY = 'eyecare:sidebar-collapsed'

function readCollapsed(): boolean {
  try {
    return localStorage.getItem(COLLAPSE_KEY) === '1'
  } catch {
    return false
  }
}

const collapsed = ref(readCollapsed())

watch(collapsed, (value) => {
  try {
    localStorage.setItem(COLLAPSE_KEY, value ? '1' : '0')
  } catch {
    /* ambiente sem localStorage — tudo bem, só não persiste */
  }
})

function toggleCollapsed(): void {
  collapsed.value = !collapsed.value
}

/** Grupos começam abertos; o usuário pode fechar cada um. */
const closedGroups = ref<Record<string, boolean>>({})

function isGroupOpen(label: string): boolean {
  return closedGroups.value[label] !== true
}

function toggleGroup(label: string): void {
  closedGroups.value = { ...closedGroups.value, [label]: isGroupOpen(label) }
}

/** Rota atual (reativa) para marcar o item ativo. */
const instance = getCurrentInstance()
const currentPath = computed(() => instance?.proxy.$route.path ?? '')

function isLeafActive(leaf: MenuLeaf): boolean {
  const name = typeof leaf.to === 'object' ? leaf.to.name : undefined

  // Paciente fica ativo em toda a área de pacientes (/pacientes, /pacientes/...).
  if (name === 'patients.list') {
    return currentPath.value.startsWith('/pacientes')
  }

  return false
}
</script>

<template>
  <div class="shell">
    <aside class="shell__sidebar" :class="{ 'shell__sidebar--collapsed': collapsed }">
      <div class="shell__brand">
        <span class="shell__logo">
          <img :src="logo" alt="Logo Eyecare" />
        </span>

        <button
          class="shell__toggle"
          type="button"
          :aria-label="collapsed ? 'Expandir menu' : 'Recolher menu'"
          @click="toggleCollapsed"
        >
          <v-icon color="white" size="22">
            {{ collapsed ? 'mdi-menu' : 'mdi-backburger' }}
          </v-icon>
        </button>
      </div>

      <!-- Expandido: grupos com rótulo e filhos -->
      <nav v-if="!collapsed" class="shell__nav">
        <div v-for="group in menu" :key="group.label" class="shell__group">
          <button
            class="shell__group-header"
            type="button"
            :aria-expanded="isGroupOpen(group.label)"
            @click="toggleGroup(group.label)"
          >
            <v-icon color="white" size="22">{{ group.icon }}</v-icon>
            <span class="shell__group-label">{{ group.label }}</span>
            <v-icon class="shell__chevron" color="white" size="20">
              {{ isGroupOpen(group.label) ? 'mdi-chevron-up' : 'mdi-chevron-down' }}
            </v-icon>
          </button>

          <div v-show="isGroupOpen(group.label)" class="shell__children">
            <router-link
              v-for="child in group.children"
              :key="child.label"
              :to="child.to"
              class="shell__nav-item shell__nav-item--leaf"
              :class="{ 'is-active': isLeafActive(child) }"
            >
              <v-icon color="white" size="20">{{ child.icon }}</v-icon>
              <span class="shell__leaf-label">{{ child.label }}</span>
            </router-link>
          </div>
        </div>
      </nav>

      <!-- Colapsado: só os ícones navegáveis -->
      <nav v-else class="shell__nav shell__nav--collapsed">
        <router-link
          v-for="leaf in leaves"
          :key="leaf.label"
          :to="leaf.to"
          class="shell__nav-item"
          :class="{ 'is-active': isLeafActive(leaf) }"
          :title="leaf.label"
        >
          <v-icon color="white" size="22">{{ leaf.icon }}</v-icon>
        </router-link>
      </nav>
    </aside>

    <div class="shell__main">
      <header class="shell__topbar" />

      <main class="shell__content">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.shell {
  display: flex;
  min-height: 100vh;
  background: #f4f6fa;
}

.shell__sidebar {
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex: 0 0 230px;
  width: 230px;
  padding: 16px 12px;
  background: #0f1b4c;
  transition:
    flex-basis 0.2s ease,
    width 0.2s ease;
}

.shell__sidebar--collapsed {
  flex: 0 0 72px;
  width: 72px;
  align-items: center;
}

/* Marca + botão de recolher */
.shell__brand {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  min-height: 48px;
}

.shell__sidebar--collapsed .shell__brand {
  flex-direction: column;
  justify-content: center;
}

.shell__logo {
  display: inline-flex;
  align-items: center;
  overflow: hidden;
  border-radius: 6px;
  background: #fff;
}

.shell__logo img {
  display: block;
  width: 130px;
  height: auto;
  object-fit: contain;
  transition: width 0.2s ease;
}

.shell__sidebar--collapsed .shell__logo img {
  width: 44px;
}

.shell__toggle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 8px;
  background: transparent;
  cursor: pointer;
  opacity: 0.8;
  transition:
    background 0.15s ease,
    opacity 0.15s ease;
}

.shell__toggle:hover {
  background: rgba(255, 255, 255, 0.16);
  opacity: 1;
}

.shell__nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.shell__nav--collapsed {
  align-items: center;
  gap: 8px;
}

.shell__group {
  display: flex;
  flex-direction: column;
}

.shell__group-header {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  border: 0;
  border-radius: 10px;
  background: transparent;
  color: #fff;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  opacity: 0.85;
  transition:
    background 0.15s ease,
    opacity 0.15s ease;
}

.shell__group-header:hover {
  background: rgba(255, 255, 255, 0.1);
  opacity: 1;
}

.shell__group-label {
  flex: 1;
  text-align: left;
}

.shell__chevron {
  flex: 0 0 auto;
}

.shell__children {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding-left: 8px;
  margin-top: 2px;
}

.shell__nav-item {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  color: #fff;
  text-decoration: none;
  opacity: 0.6;
  transition:
    opacity 0.15s ease,
    background 0.15s ease;
}

.shell__nav-item--leaf {
  justify-content: flex-start;
  gap: 12px;
  width: 100%;
  padding: 10px 12px;
  font-size: 14px;
  font-weight: 500;
}

.shell__nav-item:hover,
.shell__nav-item.is-active {
  background: rgba(255, 255, 255, 0.16);
  opacity: 1;
}

.shell__leaf-label {
  white-space: nowrap;
}

.shell__main {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.shell__topbar {
  flex: 0 0 72px;
  background: #fff;
  border-bottom: 1px solid #e8ecf4;
}

.shell__content {
  flex: 1;
  padding: 24px;
}

@media (max-width: 600px) {
  .shell__content {
    padding: 16px;
  }
}
</style>
