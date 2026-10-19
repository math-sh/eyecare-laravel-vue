import { ref } from 'vue'

import { isValidCpf } from '@/lib/cpf'
import type { BiologicalSex, Patient, PatientPayload } from '@/types/patient'
import { patientsApi } from '@/services/patients.service'

import { useToast } from '@/hooks/use-toast'
import { isApiError } from '@/services/http'
import {
  formatCellphone,
  formatCpf,
  formatDate,
  formatRg,
  onlyDigits,
  toIsoDate,
} from '@/lib/formatters'

export interface PatientFormState {
  full_name: string
  cpf: string
  rg: string
  birth_date: string
  biological_sex: BiologicalSex | null
  cellphone: string
  email: string
  description: string
}

type FormErrors = Partial<Record<keyof PatientFormState, string>>

const REQUIRED_MESSAGE = 'Campo obrigatório'

function emptyState(): PatientFormState {
  return {
    full_name: '',
    cpf: '',
    rg: '',
    birth_date: '',
    biological_sex: null,
    cellphone: '',
    email: '',
    description: '',
  }
}

export function usePatientForm() {
  const toast = useToast()

  const form = ref<PatientFormState>(emptyState())
  const errors = ref<FormErrors>({})
  const saving = ref(false)

  /** Carrega um paciente existente, ja mascarado para exibicao. */
  function fill(patient: Patient): void {
    form.value = {
      full_name: patient.full_name,
      cpf: formatCpf(patient.cpf),
      rg: formatRg(patient.rg),
      birth_date: formatDate(patient.birth_date),
      biological_sex: patient.biological_sex,
      cellphone: formatCellphone(patient.cellphone),
      email: patient.email ?? '',
      description: patient.description ?? '',
    }
    errors.value = {}
  }

  function validate(): boolean {
    const found: FormErrors = {}
    const state = form.value

    if (state.full_name.trim().length === 0) {
      found.full_name = REQUIRED_MESSAGE
    } else if (state.full_name.trim().length < 3) {
      found.full_name = 'Informe o nome completo'
    }

    if (state.cpf.trim().length === 0) {
      found.cpf = REQUIRED_MESSAGE
    } else if (!isValidCpf(state.cpf)) {
      found.cpf = 'CPF inválido'
    }

    if (state.rg.trim().length === 0) {
      found.rg = REQUIRED_MESSAGE
    }

    if (state.birth_date.trim().length === 0) {
      found.birth_date = REQUIRED_MESSAGE
    } else if (!isPastDate(state.birth_date)) {
      found.birth_date = 'Data inválida'
    }

    if (state.biological_sex === null) {
      found.biological_sex = REQUIRED_MESSAGE
    }

    if (state.cellphone.trim().length === 0) {
      found.cellphone = REQUIRED_MESSAGE
    } else if (onlyDigits(state.cellphone).length !== 11) {
      found.cellphone = 'Celular incompleto'
    }

    if (state.email.trim().length > 0 && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email.trim())) {
      found.email = 'E-mail inválido'
    }

    errors.value = found

    return Object.keys(found).length === 0
  }

  function toPayload(): PatientPayload {
    const state = form.value

    return {
      full_name: state.full_name.trim(),
      cpf: onlyDigits(state.cpf),
      rg: onlyDigits(state.rg),
      birth_date: toIsoDate(state.birth_date),
      biological_sex: state.biological_sex,
      email: state.email.trim() || null,
      cellphone: onlyDigits(state.cellphone),
      description: state.description.trim() || null,
    }
  }

  /**
   * @returns o paciente salvo, ou null se a validacao ou a API recusou.
   */
  async function submit(patientId?: string): Promise<Patient | null> {
    if (!validate()) {
      toast.error('Preencha os campos obrigatórios para salvar!')

      return null
    }

    saving.value = true

    try {
      const payload = toPayload()
      const saved = patientId
        ? await patientsApi.update(patientId, payload)
        : await patientsApi.create(payload)

      toast.success(
        patientId ? 'Paciente editado com sucesso!' : 'Paciente cadastrado com sucesso!',
      )

      return saved
    } catch (error) {
      handleSaveError(error)

      return null
    } finally {
      saving.value = false
    }
  }

  function handleSaveError(error: unknown): void {
    if (!isApiError(error)) {
      toast.error('Não foi possível salvar o paciente.')

      return
    }

    const fieldErrors = Object.entries(error.fieldErrors)

    if (fieldErrors.length > 0) {
      errors.value = Object.fromEntries(
        fieldErrors.map(([field, messages]) => [field, messages[0]]),
      ) as FormErrors

      toast.error('Preencha os campos obrigatórios para salvar!')

      return
    }

    toast.error(error.message)

    if (error.status === 409) {
      errors.value = { ...errors.value, cpf: error.message }
    }
  }

  return { form, errors, saving, fill, submit, validate }
}

function isPastDate(brDate: string): boolean {
  const iso = toIsoDate(brDate)

  if (iso === '') {
    return false
  }

  const date = new Date(`${iso}T00:00:00`)

  return (
    !Number.isNaN(date.getTime()) && date.toISOString().slice(0, 10) === iso && date < new Date()
  )
}
