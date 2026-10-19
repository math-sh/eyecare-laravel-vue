<script setup lang="ts">
import type { Patient } from '@/types/patient'

import { formatCellphone, formatCpf, formatDate } from '@/lib/formatters'

defineProps<{ patients: Patient[]; loading: boolean }>()
</script>

<template>
  <div class="table">
    <table class="table__element">
      <thead>
        <tr>
          <th>Nome do paciente</th>
          <th>CPF</th>
          <th>Celular</th>
          <th>Data de nascimento</th>
          <th class="table__actions-header"><span class="sr-only">Ações</span></th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="loading">
          <td colspan="5" class="table__message">Carregando pacientes...</td>
        </tr>

        <tr v-else-if="patients.length === 0">
          <td colspan="5" class="table__message">Nenhum paciente encontrado.</td>
        </tr>

        <tr v-for="patient in patients" v-else :key="patient.id">
          <td class="table__name">{{ patient.full_name }}</td>
          <td>{{ formatCpf(patient.cpf) }}</td>
          <td>{{ formatCellphone(patient.cellphone) }}</td>
          <td>{{ formatDate(patient.birth_date) }}</td>
          <td class="table__actions">
            <router-link
              :to="{ name: 'patients.detail', params: { id: patient.id } }"
              :aria-label="`Ver detalhes de ${patient.full_name}`"
            >
              <v-icon color="primary" size="22">mdi-eye</v-icon>
            </router-link>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table {
  overflow-x: auto;
  border: 1px solid #e8ecf4;
  border-radius: 8px;
}

.table__element {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
  white-space: nowrap;
}

.table__element th {
  padding: 18px 20px;
  background: #f7f9fc;
  color: #0f1b4c;
  font-weight: 600;
  text-align: left;
}

.table__element td {
  padding: 18px 20px;
  border-top: 1px solid #e8ecf4;
  color: #45527a;
}

.table__element tbody tr:hover td {
  background: #fafbfe;
}

.table__name {
  color: #0f1b4c !important;
  font-weight: 500;
}

.table__actions,
.table__actions-header {
  width: 64px;
  text-align: right;
}

.table__message {
  padding: 32px 20px !important;
  color: #8c97b2 !important;
  text-align: center;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
}
</style>
