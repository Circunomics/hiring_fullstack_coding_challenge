// Browser requests use the same origin as Vite; the dev server forwards /api
// to the backend container. Production builds may still set a public API URL.
export const API_BASE = import.meta.env.DEV ? '' : (import.meta.env.VITE_API_URL ?? '')

export class ApiError extends Error {
  readonly status: number
  readonly body: unknown

  constructor(message: string, status: number, body: unknown) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.body = body
  }
}

export async function apiFetch<T>(path: string, init?: RequestInit): Promise<T> {
  const response = await fetch(`${API_BASE}${path}`, {
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(init?.headers ?? {}) },
    ...init,
  })

  const contentType = response.headers.get('content-type') ?? ''
  const isJson = contentType.includes('application/json')
  const body: unknown = isJson ? await response.json() : await response.text()

  if (!response.ok) {
    throw new ApiError(extractErrorMessage(body, path, response.status), response.status, body)
  }

  return body as T
}

function extractErrorMessage(body: unknown, path: string, status: number): string {
  const fallback = `Request to ${path} failed with status ${status}`
  if (body === null || typeof body !== 'object') return fallback

  const record = body as { error?: unknown; message?: unknown }
  // Our API's shape: { error: { code, message } }
  if (record.error !== null && typeof record.error === 'object') {
    const errorObject = record.error as { message?: unknown }
    if (typeof errorObject.message === 'string' && errorObject.message !== '') {
      return errorObject.message
    }
  }
  // Fallback: { error: "…" }
  if (typeof record.error === 'string' && record.error !== '') return record.error
  // Fallback: { message: "…" }
  if (typeof record.message === 'string' && record.message !== '') return record.message

  return fallback
}
