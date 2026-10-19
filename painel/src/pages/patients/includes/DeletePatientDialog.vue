<script setup lang="ts">
import { ref } from 'vue'

import { patientsApi } from '@/services/patients.service'

import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import { useToast } from '@/hooks/use-toast'
import { isApiError } from '@/services/http'

const props = defineProps<{ value: boolean; patientId: string }>()

const emit = defineEmits<{
  (event: 'input', value: boolean): void
  (event: 'deleted'): void
}>()

const toast = useToast()
const deleting = ref(false)

async function confirm(): Promise<void> {
  deleting.value = true

  try {
    await patientsApi.remove(props.patientId)

    emit('input', false)
    toast.success('Paciente excluído com sucesso!')
    emit('deleted')
  } catch (error) {
    toast.error(isApiError(error) ? error.message : 'Não foi possível excluir o paciente.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <ConfirmDialog
    :value="value"
    title="Excluir paciente"
    confirm-label="Confirmar"
    :loading="deleting"
    @input="emit('input', $event)"
    @confirm="confirm"
  >
    Atenção! Ao excluir este paciente, <strong>essa ação não poderá ser desfeita</strong>. Deseja
    realmente continuar?
  </ConfirmDialog>
</template>
