<script setup lang="ts">
import PatientsTable from './includes/PatientsTable.vue'

import { usePatients } from './includes/use-patients'

const { patients, search, page, lastPage, loading } = usePatients()
</script>

<template>
  <div>
    <header class="page-header">
      <h1 class="page-header__title">Pacientes</h1>

      <v-btn color="primary" depressed large :to="{ name: 'patients.create' }">
        Novo paciente
      </v-btn>
    </header>

    <section class="card">
      <label class="search">
        <v-icon size="22" color="#8C97B2">mdi-magnify</v-icon>
        <input
          v-model="search"
          class="search__input"
          type="search"
          placeholder="Buscar por nome do paciente ou CPF"
          aria-label="Buscar por nome do paciente ou CPF"
        />
      </label>

      <PatientsTable :patients="patients" :loading="loading" />

      <div v-if="lastPage > 1" class="pagination">
        <v-pagination
          v-model="page"
          :length="lastPage"
          :total-visible="5"
          color="primary"
          prev-icon="mdi-chevron-left"
          next-icon="mdi-chevron-right"
        />
      </div>
    </section>
  </div>
</template>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.page-header__title {
  margin: 0;
  color: #0f1b4c;
  font-size: 26px;
  font-weight: 700;
}

.card {
  padding: 24px;
  background: #fff;
  border-radius: 8px;
}

.search {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  padding: 12px 16px;
  border: 1px solid #e8ecf4;
  border-radius: 8px;
  transition: border-color 0.15s ease;
}

.search:focus-within {
  border-color: #2f54eb;
}

.search__input {
  flex: 1;
  border: 0;
  outline: none;
  color: #0f1b4c;
  font-size: 15px;
}

.search__input::placeholder {
  color: #8c97b2;
}

.pagination {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}
</style>
