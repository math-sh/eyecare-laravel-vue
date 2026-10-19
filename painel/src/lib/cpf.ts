/**
 * Mesma regra do value object Cpf no back-end.
 *
 * Esta duplicacao e deliberada: o front valida para dar resposta imediata
 * (o prototipo mostra o erro sem ida ao servidor), mas quem garante a regra
 * continua sendo a API — o front e so UX.
 */
export function isValidCpf(value: string): boolean {
  const digits = value.replace(/\D/g, '')

  if (digits.length !== 11 || /^(\d)\1{10}$/.test(digits)) {
    return false
  }

  for (const position of [9, 10]) {
    let sum = 0

    for (let i = 0; i < position; i++) {
      sum += Number(digits[i]) * (position + 1 - i)
    }

    if (Number(digits[position]) !== ((10 * sum) % 11) % 10) {
      return false
    }
  }

  return true
}
