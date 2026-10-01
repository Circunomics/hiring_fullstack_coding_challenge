import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { apiFetch } from '@/shared/api/http'
import type { ImportResult, Repository } from '../types'

const REPOSITORIES_KEY = ['repositories'] as const

export function useRepositories() {
  return useQuery({
    queryKey: REPOSITORIES_KEY,
    queryFn: async () => {
      const data = await apiFetch<{ items: Repository[] }>('/api/v1/repositories')
      return data.items
    },
  })
}

export function useImportRepository() {
  const client = useQueryClient()
  return useMutation({
    mutationFn: (payload: { provider: string; slug: string }) =>
      apiFetch<ImportResult>('/api/v1/repositories', {
        method: 'POST',
        body: JSON.stringify(payload),
      }),
    onSettled: () => {
      void client.invalidateQueries({ queryKey: REPOSITORIES_KEY })
    },
  })
}

export function useSyncRepository() {
  const client = useQueryClient()
  return useMutation({
    mutationFn: (id: number) =>
      apiFetch<ImportResult>(`/api/v1/repositories/${id}/sync`, { method: 'POST' }),
    onSettled: (_result, _error, id) => {
      void client.invalidateQueries({ queryKey: REPOSITORIES_KEY })
      void client.invalidateQueries({ queryKey: ['repositories', id, 'contributors'] })
    },
  })
}
