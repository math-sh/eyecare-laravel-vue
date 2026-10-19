<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import DeletePatientDialog from './includes/DeletePatientDialog.vue'

import { biologicalSexLabel, type Patient } from '@/types/patient'
import { patientsApi } from '@/services/patients.service'

import AppBreadcrumb, { type Crumb } from '@/components/layout/AppBreadcrumb.vue'
import { useToast } from '@/hooks/use-toast'
import { isApiError } from '@/services/http'
import router from '@/Router'
import { formatCellphone, formatCpf, formatDate, formatRg } from '@/lib/formatters'

const props = defineProps<{ id: string }>()

const toast = useToast()

const patient = ref<Patient | null>(null)
const loading = ref(true)
const notFound = ref(false)
const showDeleteDialog = ref(false)

const breadcrumb = computed<Crumb[]>(() => [
  { label: 'Pacientes', to: { name: 'patients.list' } },
  { label: patient.value?.full_name ?? 'Paciente' },
])

/** Os mesmos campos do formulário, em modo leitura. */
const fields = computed(() => {
  const current = patient.value

  if (!current) {
    return []
  }

  return [
    { label: 'Nome completo', value: current.full_name, wide: true },
    { label: 'CPF', value: formatCpf(current.cpf) },
    { label: 'RG', value: formatRg(current.rg) },
    { label: 'Data de nascimento', value: formatDate(current.birth_date) },
    { label: 'Sexo biológico', value: biologicalSexLabel(current.biological_sex) },
    { label: 'Celular', value: formatCellphone(current.cellphone) },
    { label: 'E-mail', value: current.email ?? '—' },
  ]
})

onMounted(async () => {
  try {
    patient.value = await patientsApi.find(props.id)
  } catch (error) {
    notFound.value = isApiError(error) && error.status === 404

    if (!notFound.value) {
      toast.error(isApiError(error) ? error.message : 'Não foi possível carregar o paciente.')
    }
  } finally {
    loading.value = false
  }
})

function onDeleted(): void {
  void router.push({ name: 'patients.list' })
}
</script>

<template>
  <div>
    <AppBreadcrumb :items="breadcrumb" />

    <section class="card">
      <template v-if="loading">
        <p class="card__message">Carregando paciente...</p>
      </template>

      <template v-else-if="notFound">
        <h1 class="card__title">Paciente não encontrado</h1>
        <p class="card__message">
          Este paciente não existe ou foi excluído.
          <router-link :to="{ name: 'patients.list' }">Voltar para a listagem</router-link>
        </p>
      </template>

      <template v-else-if="patient">
        <header class="card__header">
          <h1 class="card__title">{{ patient.full_name }}</h1>

          <v-btn
            color="primary"
            depressed
            large
            :to="{ name: 'patients.edit', params: { id: patient.id } }"
          >
            Editar paciente
          </v-btn>
        </header>

        <div class="card__divider" />

        <dl class="details">
          <div
            v-for="field in fields"
            :key="field.label"
            class="details__item"
            :class="{ 'details__item--wide': field.wide }"
          >
            <dt class="details__label">{{ field.label }}</dt>
            <dd class="details__value">{{ field.value }}</dd>
          </div>
        </dl>

        <div class="details__description">
          <dt class="details__label">Descrição</dt>
          <dd class="details__value details__value--text">
            {{ patient.description || '—' }}
          </dd>
        </div>

        <footer class="card__footer">
          <button class="link-danger" type="button" @click="showDeleteDialog = true">
            Excluir paciente
          </button>

          <router-link class="link-back" :to="{ name: 'patients.list' }">
            Voltar para a listagem
          </router-link>
        </footer>

        <DeletePatientDialog
          v-model="showDeleteDialog"
          :patient-id="patient.id"
          @deleted="onDeleted"
        />
      </template>
    </section>
  </div>
</template>

<style scoped>
.card {
  display: flex;
  flex-direction: column;
  min-height: 640px;
  padding: 28px;
  background: #fff;
  border-radius: 8px;
}

.card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.card__title {
  margin: 0;
  color: #0f1b4c;
  font-size: 22px;
  font-weight: 700;
}

.card__divider {
  margin: 18px 0 24px;
  border-top: 1px solid #e8ecf4;
}

.card__message {
  color: #8c97b2;
}

.details {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
  margin: 0 0 20px;
}

.details__item--wide {
  grid-column: span 2;
}

.details__label {
  margin-bottom: 4px;
  color: #8c97b2;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.details__value {
  margin: 0;
  color: #0f1b4c;
  font-size: 16px;
}

.details__value--text {
  line-height: 1.6;
}

.card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-top: auto;
  padding-top: 32px;
}

.link-danger {
  background: none;
  border: 0;
  color: #f5222d;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
}

.link-back {
  color: #2f54eb;
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

@media (max-width: 960px) {
  .details {
    grid-template-columns: 1fr;
  }

  .details__item--wide {
    grid-column: span 1;
  }
}
</style>
