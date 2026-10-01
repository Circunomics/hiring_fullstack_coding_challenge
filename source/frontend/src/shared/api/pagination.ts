export interface PaginatedResponse<T> {
  items: T[]
  total: number
  page: number
  perPage: number
}

export const totalPages = (total: number, perPage: number): number => Math.max(1, Math.ceil(total / perPage))
