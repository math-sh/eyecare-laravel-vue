/**
 * Formatacao para exibicao. A API sempre devolve valores crus (so digitos,
 * data ISO), entao mascarar e responsabilidade de quem mostra.
 */

export function onlyDigits(value: string): string {
  return value.replace(/\D/g, '')
}

export function formatCpf(cpf: string): string {
  const digits = onlyDigits(cpf).slice(0, 11)

  return digits
    .replace(/^(\d{3})(\d)/, '$1.$2')
    .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2')
}

export function formatRg(rg: string): string {
  const digits = onlyDigits(rg).slice(0, 9)

  return digits
    .replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4')
}

export function formatCellphone(cellphone: string): string {
  const digits = onlyDigits(cellphone).slice(0, 11)

  if (digits.length <= 2) {
    return digits.replace(/^(\d{1,2})/, '($1')
  }

  return digits.replace(/^(\d{2})(\d{1,5})(\d{0,4})$/, (_, ddd, prefix, suffix) =>
    suffix ? `(${ddd}) ${prefix}-${suffix}` : `(${ddd}) ${prefix}`,
  )
}

/** '1996-07-20' -> '20/07/1996' */
export function formatDate(isoDate: string): string {
  const [year, month, day] = isoDate.split('-')

  return year && month && day ? `${day}/${month}/${year}` : isoDate
}

/** '20/07/1996' -> '1996-07-20' (formato que a API espera) */
export function toIsoDate(brDate: string): string {
  const digits = onlyDigits(brDate)

  if (digits.length !== 8) {
    return ''
  }

  return `${digits.slice(4, 8)}-${digits.slice(2, 4)}-${digits.slice(0, 2)}`
}

/** Mascara progressiva de data enquanto o usuario digita: DD/MM/AAAA */
export function formatDateInput(value: string): string {
  const digits = onlyDigits(value).slice(0, 8)

  return digits.replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3')
}
