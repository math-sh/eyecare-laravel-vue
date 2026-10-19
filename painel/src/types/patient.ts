export type BiologicalSex = 'male' | 'female'

export const BIOLOGICAL_SEX_OPTIONS: Array<{ value: BiologicalSex; label: string }> = [
  { value: 'male', label: 'Masculino' },
  { value: 'female', label: 'Feminino' },
]

export function biologicalSexLabel(sex: BiologicalSex): string {
  return BIOLOGICAL_SEX_OPTIONS.find((option) => option.value === sex)?.label ?? ''
}

/** Paciente como a API devolve: valores crus, sem mascara. */
export interface Patient {
  id: string
  full_name: string
  cpf: string
  rg: string
  birth_date: string
  biological_sex: BiologicalSex
  cellphone: string
  email: string | null
  description: string | null
  created_at: string
  updated_at: string
}

/** Corpo enviado no cadastro e na edicao. */
export interface PatientPayload {
  full_name: string
  cpf: string
  rg: string
  birth_date: string
  biological_sex: BiologicalSex | null
  cellphone: string
  email: string | null
  description: string | null
}

export interface PatientFilters {
  search: string
  page: number
  perPage: number
}

export interface Paginated<T> {
  data: T[]
  meta: {
    total: number
    page: number
    per_page: number
    last_page: number
  }
}
