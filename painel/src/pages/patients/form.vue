<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

import DeletePatientDialog from './includes/DeletePatientDialog.vue'

import { usePatientForm } from './includes/use-patient-form'
import { BIOLOGICAL_SEX_OPTIONS } from '@/types/patient'
import { patientsApi } from '@/services/patients.service'

import AppBreadcrumb, { type Crumb } from '@/components/layout/AppBreadcrumb.vue'
import FormField from '@/components/ui/FormField.vue'
import { useToast } from '@/hooks/use-toast'
import { isApiError } from '@/services/http'
import router from '@/Router'
import { formatCellphone, formatCpf, formatDateInput, formatRg, toIsoDate } from '@/lib/formatters'

const props = defineProps<{ id?: string }>()

const toast = useToast()
const { form, errors, saving, fill, submit } = usePatientForm()

const loading = ref(false)
const patientName = ref('')
const showDeleteDialog = ref(false)
const showDatePicker = ref(false)

const isEditing = computed(() => Boolean(props.id))

const breadcrumb = computed<Crumb[]>(() =>
  isEditing.value
    ? [
        { label: 'Pacientes', to: { name: 'patients.list' } },
        { label: patientName.value, to: { name: 'patients.detail', params: { id: props.id } } },
        { label: 'Editar paciente' },
      ]
    : [{ label: 'Pacientes', to: { name: 'patients.list' } }, { label: 'Novo paciente' }],
)

/** O date picker trabalha em ISO; o campo de texto, em DD/MM/AAAA. */
const isoBirthDate = computed({
  get: () => toIsoDate(form.value.birth_date),
  set: (value: string) => {
    form.value.birth_date = value ? formatDateInput(value.split('-').reverse().join('')) : ''
  },
})

onMounted(async () => {
  if (!props.id) {
    return
  }

  loading.value = true

  try {
    const patient = await patientsApi.find(props.id)

    patientName.value = patient.full_name
    fill(patient)
  } catch (error) {
    toast.error(isApiError(error) ? error.message : 'Não foi possível carregar o paciente.')
    await router.push({ name: 'patients.list' })
  } finally {
    loading.value = false
  }
})

/**
 * As mascaras moram aqui, e nao em expressoes do template: o compilador de
 * template do Vue 2 gera a render function sem passar pelo TypeScript, entao
 * um cast como ($event.target as HTMLInputElement) quebraria o build.
 */
function inputValue(event: Event): string {
  return (event.target as HTMLInputElement).value
}

function onCpfInput(event: Event): void {
  form.value.cpf = formatCpf(inputValue(event))
}

function onRgInput(event: Event): void {
  form.value.rg = formatRg(inputValue(event))
}

function onCellphoneInput(event: Event): void {
  form.value.cellphone = formatCellphone(inputValue(event))
}

function onBirthDateInput(event: Event): void {
  form.value.birth_date = formatDateInput(inputValue(event))
}

const today = new Date().toISOString().slice(0, 10)

async function save(): Promise<void> {
  const saved = await submit(props.id)

  if (saved) {
    await router.push({ name: 'patients.list' })
  }
}

function onDeleted(): void {
  void router.push({ name: 'patients.list' })
}
</script>

<template>
  <div>
    <AppBreadcrumb :items="breadcrumb" />

    <form class="card" novalidate @submit.prevent="save">
      <h1 class="card__title">
        {{ isEditing ? 'Editar paciente' : 'Cadastro de novo paciente' }}
      </h1>

      <div class="card__divider" />

      <div v-if="loading" class="card__loading">Carregando paciente...</div>

      <template v-else>
        <div class="grid grid--identification">
          <FormField label="Nome completo" input-id="full_name" :error="errors.full_name">
            <input
              id="full_name"
              v-model="form.full_name"
              class="input"
              :class="{ 'input--error': errors.full_name }"
              type="text"
              placeholder="Descrever"
              maxlength="255"
            />
          </FormField>

          <FormField label="CPF" input-id="cpf" :error="errors.cpf">
            <input
              id="cpf"
              class="input"
              :class="{ 'input--error': errors.cpf }"
              type="text"
              inputmode="numeric"
              placeholder="000.000.000-00"
              maxlength="14"
              :value="form.cpf"
              @input="onCpfInput"
            />
          </FormField>

          <FormField label="RG" input-id="rg" :error="errors.rg">
            <input
              id="rg"
              class="input"
              :class="{ 'input--error': errors.rg }"
              type="text"
              inputmode="numeric"
              placeholder="00.000.000-0"
              maxlength="12"
              :value="form.rg"
              @input="onRgInput"
            />
          </FormField>
        </div>

        <div class="grid grid--contact">
          <FormField label="Data de nascimento" input-id="birth_date" :error="errors.birth_date">
            <div class="input-with-icon">
              <input
                id="birth_date"
                class="input"
                :class="{ 'input--error': errors.birth_date }"
                type="text"
                inputmode="numeric"
                placeholder="DD/MM/AAAA"
                maxlength="10"
                :value="form.birth_date"
                @input="onBirthDateInput"
              />

              <v-menu
                v-model="showDatePicker"
                :close-on-content-click="false"
                transition="scale-transition"
                min-width="auto"
                offset-y
              >
                <template #activator="{ on, attrs }">
                  <button
                    class="input-with-icon__button"
                    type="button"
                    aria-label="Escolher data no calendário"
                    v-bind="attrs"
                    v-on="on"
                  >
                    <v-icon size="20" color="#2F54EB">mdi-calendar-blank-outline</v-icon>
                  </button>
                </template>

                <v-date-picker
                  v-model="isoBirthDate"
                  locale="pt-BR"
                  color="primary"
                  :max="today"
                  @input="showDatePicker = false"
                />
              </v-menu>
            </div>
          </FormField>

          <FormField
            label="Sexo biológico"
            input-id="biological_sex"
            :error="errors.biological_sex"
          >
            <div class="select-wrapper">
              <select
                id="biological_sex"
                v-model="form.biological_sex"
                class="select"
                :class="{ 'select--error': errors.biological_sex }"
                required
              >
                <option :value="null" disabled>Selecionar</option>
                <option
                  v-for="option in BIOLOGICAL_SEX_OPTIONS"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
            </div>
          </FormField>

          <FormField label="Celular" input-id="cellphone" :error="errors.cellphone">
            <input
              id="cellphone"
              class="input"
              :class="{ 'input--error': errors.cellphone }"
              type="text"
              inputmode="numeric"
              placeholder="(00) 00000-0000"
              maxlength="15"
              :value="form.cellphone"
              @input="onCellphoneInput"
            />
          </FormField>

          <FormField label="E-mail" input-id="email" optional :error="errors.email">
            <input
              id="email"
              v-model="form.email"
              class="input"
              :class="{ 'input--error': errors.email }"
              type="email"
              placeholder="Descrever"
              maxlength="255"
            />
          </FormField>
        </div>

        <FormField label="Descrição" input-id="description" optional :error="errors.description">
          <textarea
            id="description"
            v-model="form.description"
            class="textarea"
            placeholder="Descrever"
            maxlength="2000"
          />
        </FormField>
      </template>

      <footer class="card__footer">
        <button
          v-if="isEditing"
          class="link-danger"
          type="button"
          :disabled="saving"
          @click="showDeleteDialog = true"
        >
          Excluir paciente
        </button>

        <router-link v-else class="link-danger" :to="{ name: 'patients.list' }">
          Cancelar
        </router-link>

        <v-btn color="primary" depressed large type="submit" :loading="saving" :disabled="loading">
          {{ isEditing ? 'Atualizar paciente' : 'Salvar paciente' }}
        </v-btn>
      </footer>
    </form>

    <DeletePatientDialog
      v-if="isEditing && id"
      v-model="showDeleteDialog"
      :patient-id="id"
      @deleted="onDeleted"
    />
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

.card__loading {
  color: #8c97b2;
}

.grid {
  display: grid;
  gap: 16px 20px;
  margin-bottom: 20px;
}

/* Nome completo largo, CPF e RG ao lado — como no protótipo. */
.grid--identification {
  grid-template-columns: minmax(0, 2.6fr) minmax(0, 1fr) minmax(0, 1fr);
}

.grid--contact {
  grid-template-columns: minmax(0, 0.8fr) minmax(0, 0.9fr) minmax(0, 0.9fr) minmax(0, 1.6fr);
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
  text-decoration: none;
}

.link-danger:disabled {
  opacity: 0.5;
  cursor: default;
}

@media (max-width: 960px) {
  .grid--identification,
  .grid--contact {
    grid-template-columns: 1fr;
  }
}
</style>
