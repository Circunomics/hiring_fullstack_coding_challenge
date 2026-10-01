export type ContributorSort = 'commits' | 'name' | 'lastCommittedAt'
export type SortDirection = 'asc' | 'desc'

export interface Contributor {
  id: number
  displayName: string
  email: string | null
  avatarUrl: string | null
  commitCount: number
  lastCommittedAt: string
}

export interface ContributorCommit {
  id: number
  sha: string
  shortSha: string
  message: string
  committedAt: string
  htmlUrl: string
  authorName: string
  authorEmail: string | null
}

export interface ContributorFilters {
  search: string
  from: string | null
  to: string | null
  sort: ContributorSort
  direction: SortDirection
  page: number
  perPage: number
}

export const DEFAULT_FILTERS: ContributorFilters = {
  search: '',
  from: null,
  to: null,
  sort: 'commits',
  direction: 'desc',
  page: 1,
  perPage: 20,
}
