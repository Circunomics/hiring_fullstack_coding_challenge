import { useQuery, keepPreviousData } from '@tanstack/react-query'
import { apiFetch } from '@/shared/api/http'
import type { PaginatedResponse } from '@/shared/api/pagination'
import type { Contributor, ContributorCommit, ContributorFilters } from '../types'

function buildQuery(filters: ContributorFilters): string {
  const params = new URLSearchParams()
  if (filters.search.trim() !== '') params.set('search', filters.search.trim())
  if (filters.from) params.set('from', filters.from)
  if (filters.to) params.set('to', filters.to)
  params.set('sort', filters.sort)
  params.set('direction', filters.direction)
  params.set('page', String(filters.page))
  params.set('perPage', String(filters.perPage))
  return params.toString()
}

export function useContributors(repositoryId: number, filters: ContributorFilters) {
  return useQuery({
    queryKey: ['repositories', repositoryId, 'contributors', filters],
    queryFn: () =>
      apiFetch<PaginatedResponse<Contributor>>(
        `/api/v1/repositories/${repositoryId}/contributors?${buildQuery(filters)}`,
      ),
    placeholderData: keepPreviousData,
    enabled: Number.isSafeInteger(repositoryId) && repositoryId > 0,
  })
}

export function useContributorCommits(
  repositoryId: number,
  contributorId: number,
  page: number,
  perPage: number,
) {
  return useQuery({
    queryKey: ['repositories', repositoryId, 'contributors', contributorId, 'commits', { page, perPage }],
    queryFn: () =>
      apiFetch<PaginatedResponse<ContributorCommit>>(
        `/api/v1/repositories/${repositoryId}/contributors/${contributorId}/commits?page=${page}&perPage=${perPage}`,
      ),
    placeholderData: keepPreviousData,
    enabled:
      Number.isSafeInteger(repositoryId) && repositoryId > 0 &&
      Number.isSafeInteger(contributorId) && contributorId > 0,
  })
}
