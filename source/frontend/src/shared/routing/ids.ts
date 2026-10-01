export function isPositiveInteger(value: number): boolean {
  return Number.isSafeInteger(value) && value > 0
}

export function readPositiveInteger(value: string | null, fallback: number, maximum = Number.MAX_SAFE_INTEGER): number {
  if (value === null || !/^\d+$/.test(value)) return fallback

  const parsed = Number(value)
  return isPositiveInteger(parsed) && parsed <= maximum ? parsed : fallback
}
