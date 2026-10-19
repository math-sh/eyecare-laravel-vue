import { onMounted, ref, watch } from 'vue'

import type { Patient } from '@/types/patient'
import { patientsApi } from '@/services/patients.service'

import { useToast } from '@/hooks/use-toast'
import { isApiError } from '@/services/http'

const SEARCH_DEBOUNCE_MS = 300

export function usePatients(perPage = 10) {
  const toast = useToast()

  const patients = ref<Patient[]>([])
  const search = ref('')
  const page = ref(1)
  const total = ref(0)
  const lastPage = ref(1)
  const loading = ref(false)

  let debounceId: number | undefined

  async function fetchPatients(): Promise<void> {
    loading.value = true

    try {
      const result = await patientsApi.list({ search: search.value, page: page.value, perPage })

      patients.value = result.data
      total.value = result.meta.total
      lastPage.value = result.meta.last_page
    } catch (error) {
      patients.value = []
      total.value = 0
      toast.error(isApiError(error) ? error.message : 'Não foi possível carregar os pacientes.')
    } finally {
      loading.value = false
    }
  }

  watch(search, () => {
    window.clearTimeout(debounceId)

    debounceId = window.setTimeout(() => {
      if (page.value === 1) {
        void fetchPatients()
      } else {
        page.value = 1
      }
    }, SEARCH_DEBOUNCE_MS)
  })

  watch(page, () => void fetchPatients())

  onMounted(() => void fetchPatients())

  return { patients, search, page, total, lastPage, loading, fetchPatients }
}
