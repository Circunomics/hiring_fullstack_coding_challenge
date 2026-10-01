import { useCallback } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import { ContributorFiltersBar } from '../components/ContributorFilters'
import { ContributorTable } from '../components/ContributorTable'
import { useContributors } from '../api/contributorsApi'
import { DEFAULT_FILTERS, type ContributorFilters, type ContributorSort, type SortDirection } from '../types'
import { StateContainer } from '@/shared/components/StateContainer'
import { Pagination } from '@/shared/components/Pagination'
import { InvalidRouteState } from '@/shared/components/InvalidRouteState'
import { isPositiveInteger, readPositiveInteger } from '@/shared/routing/ids'

const SORT_OPTIONS: ContributorSort[] = ['commits', 'name', 'lastCommittedAt']

function readFilters(params: URLSearchParams): ContributorFilters {
  const sort = params.get('sort') as ContributorSort | null
  const direction = params.get('direction') as SortDirection | null

  return {
    search: params.get('search')?.trim() ?? '',
    from: params.get('from') || null,
    to: params.get('to') || null,
    sort: sort && SORT_OPTIONS.includes(sort) ? sort : DEFAULT_FILTERS.sort,
    direction: direction === 'asc' || direction === 'desc' ? direction : DEFAULT_FILTERS.direction,
    page: readPositiveInteger(params.get('page'), DEFAULT_FILTERS.page),
    perPage: readPositiveInteger(params.get('perPage'), DEFAULT_FILTERS.perPage, 100),
  }
}

function writeFilter(params: URLSearchParams, key: keyof ContributorFilters, value: string | number | null, defaultValue: string | number | null) {
  if (value === null || value === defaultValue || value === '') {
    params.delete(key)
  } else {
    params.set(key, String(value))
  }
}

export function ContributorsPage() {
  const routeParams = useParams<{ repositoryId: string }>()
  const repositoryId = Number(routeParams.repositoryId)
  const isValidRepositoryId = isPositiveInteger(repositoryId)
  const [searchParams, setSearchParams] = useSearchParams()
  const filters = readFilters(searchParams)

  const query = useContributors(repositoryId, filters)

  const patchFilters = useCallback((change: Partial<ContributorFilters>, replace = false) => {
    const nextFilters = { ...readFilters(searchParams), ...change }
    const nextParams = new URLSearchParams(searchParams)
    writeFilter(nextParams, 'search', nextFilters.search, '')
    writeFilter(nextParams, 'from', nextFilters.from, null)
    writeFilter(nextParams, 'to', nextFilters.to, null)
    writeFilter(nextParams, 'sort', nextFilters.sort, DEFAULT_FILTERS.sort)
    writeFilter(nextParams, 'direction', nextFilters.direction, DEFAULT_FILTERS.direction)
    writeFilter(nextParams, 'page', nextFilters.page, DEFAULT_FILTERS.page)
    writeFilter(nextParams, 'perPage', nextFilters.perPage, DEFAULT_FILTERS.perPage)
    setSearchParams(nextParams, { replace })
  }, [searchParams, setSearchParams])

  const handleFilterChange = useCallback((change: Partial<ContributorFilters>) => {
    patchFilters(change, 'search' in change)
  }, [patchFilters])

  if (!isValidRepositoryId) {
    return (
      <InvalidRouteState
        title="Repository not found"
        message="The repository link is invalid. Choose a tracked repository to continue."
        backTo="/"
        backLabel="Back to repositories"
      />
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <Link to="/" className="text-sm font-medium text-blue-700 hover:text-blue-900 hover:underline">
            ← All repositories
          </Link>
          <p className="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Repository activity</p>
          <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-950">Contributors</h1>
          <p className="mt-1 text-sm text-slate-600">Search people, narrow the time range, and explore their commits.</p>
        </div>
      </div>

      <ContributorFiltersBar filters={filters} onChange={handleFilterChange} />

      <section aria-busy={query.isFetching} className="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-[0_12px_40px_-25px_rgba(15,23,42,0.35)] sm:p-6">
        {query.isFetching && query.isPlaceholderData ? (
          <p role="status" className="mb-3 text-xs font-medium text-blue-700">Updating results…</p>
        ) : null}
        <StateContainer
          isLoading={query.isLoading}
          error={query.error}
          isEmpty={query.data?.items.length === 0}
          emptyText="No contributors match these filters."
          loadingText="Loading contributors…"
        >
          {query.data ? (
            <>
              <ContributorTable repositoryId={repositoryId} contributors={query.data.items} />
              <Pagination
                page={filters.page}
                perPage={filters.perPage}
                total={query.data.total}
                onPageChange={(page) => patchFilters({ page })}
              />
            </>
          ) : null}
        </StateContainer>
      </section>
    </div>
  )
}
