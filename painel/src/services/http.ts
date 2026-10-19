import axios, { AxiosError } from 'axios'

/**
 * Erro ja traduzido da API, no formato que as telas consomem.
 *
 * O painel nunca lida com AxiosError: o interceptor abaixo converte tudo
 * (inclusive falha de rede) para esta forma.
 */
export interface ApiError {
  status: number
  message: string
  /** Erros por campo vindos do 422 do Laravel. */
  fieldErrors: Record<string, string[]>
}

interface LaravelErrorBody {
  message?: string
  error?: string
  errors?: Record<string, string[]>
}

export const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  timeout: 15000,
})

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<LaravelErrorBody>) => {
    const status = error.response?.status ?? 0
    const body = error.response?.data

    return Promise.reject<ApiError>({
      status,
      message: body?.message ?? fallbackMessage(status),
      fieldErrors: body?.errors ?? {},
    })
  },
)

function fallbackMessage(status: number): string {
  if (status === 0) {
    return 'Não foi possível falar com o servidor. Verifique se a API está no ar.'
  }

  return 'Ocorreu um erro inesperado. Tente novamente.'
}

export function isApiError(error: unknown): error is ApiError {
  return typeof error === 'object' && error !== null && 'status' in error && 'fieldErrors' in error
}
