import type { Paginated, Patient, PatientFilters, PatientPayload } from '@/types/patient'

import { apiClient } from '@/services/http'

/** Unico ponto do modulo que conhece as rotas HTTP da API. */
export const patientsApi = {
  async list(filters: PatientFilters): Promise<Paginated<Patient>> {
    const { data } = await apiClient.get<Paginated<Patient>>('/patients', {
      params: {
        search: filters.search || undefined,
        page: filters.page,
        per_page: filters.perPage,
      },
    })

    return data
  },

  async find(id: string): Promise<Patient> {
    const { data } = await apiClient.get<{ data: Patient }>(`/patients/${id}`)

    return data.data
  },

  async create(payload: PatientPayload): Promise<Patient> {
    const { data } = await apiClient.post<{ data: Patient }>('/patients', payload)

    return data.data
  },

  async update(id: string, payload: PatientPayload): Promise<Patient> {
    const { data } = await apiClient.put<{ data: Patient }>(`/patients/${id}`, payload)

    return data.data
  },

  async remove(id: string): Promise<void> {
    await apiClient.delete(`/patients/${id}`)
  },
}
