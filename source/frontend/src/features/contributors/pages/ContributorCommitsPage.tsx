import { useCallback } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import { useContributorCommits } from '../api/contributorsApi'
import { StateContainer } from '@/shared/components/StateContainer'
import { Pagination } from '@/shared/components/Pagination'
import { InvalidRouteState } from '@/shared/components/InvalidRouteState'
import { isPositiveInteger, readPositiveInteger } from '@/shared/routing/ids'

export function ContributorCommitsPage() {
  const routeParams = useParams<{ repositoryId: string; contributorId: string }>()
  const repositoryId = Number(routeParams.repositoryId)
  const contributorId = Number(routeParams.contributorId)
  const isValidRepositoryId = isPositiveInteger(repositoryId)
  const isValidContributorId = isPositiveInteger(contributorId)
  const [searchParams, setSearchParams] = useSearchParams()
  const page = readPositiveInteger(searchParams.get('page'), 1)
  const perPage = 20

  const query = useContributorCommits(repositoryId, contributorId, page, perPage)
  const handlePageChange = useCallback((nextPage: number) => {
    const nextParams = new URLSearchParams(searchParams)
    if (nextPage === 1) nextParams.delete('page')
    else nextParams.set('page', String(nextPage))
    setSearchParams(nextParams)
  }, [searchParams, setSearchParams])

  if (!isValidRepositoryId || !isValidContributorId) {
    return (
      <InvalidRouteState
        title="Contributor not found"
        message="The contributor link is invalid or no longer available."
        backTo={isValidRepositoryId ? `/repositories/${repositoryId}` : '/'}
        backLabel={isValidRepositoryId ? 'Back to contributors' : 'Back to repositories'}
      />
    )
  }

  return (
    <div className="space-y-4">
      <div>
        <Link to={`/repositories/${repositoryId}`} className="text-sm font-medium text-blue-700 hover:text-blue-900 hover:underline">
          ← Contributors
        </Link>
        <p className="mt-5 text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Contributor activity</p>
        <h1 className="mt-1 text-3xl font-bold tracking-tight text-slate-950">Commit history</h1>
        <p className="mt-1 text-sm text-slate-600">Recent commits from this contributor in the tracked repository.</p>
      </div>

      <section aria-busy={query.isFetching} className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-[0_12px_40px_-25px_rgba(15,23,42,0.35)] sm:p-6">
        {query.isFetching && query.isPlaceholderData ? (
          <p role="status" className="mb-3 text-xs font-medium text-blue-700">Updating commits…</p>
        ) : null}
        <StateContainer
          isLoading={query.isLoading}
          error={query.error}
          isEmpty={query.data?.items.length === 0}
          emptyText="This contributor has no commits in the imported range."
          loadingText="Loading commits…"
        >
          {query.data ? (
            <>
              <ul className="divide-y divide-slate-100">
                {query.data.items.map((commit) => (
                  <li key={commit.id} className="py-4 first:pt-1">
                    <div className="flex items-start justify-between gap-4">
                      <div className="min-w-0">
                        <a
                          href={commit.htmlUrl}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="rounded-md bg-blue-50 px-2 py-1 font-mono text-xs font-semibold text-blue-800 hover:bg-blue-100 hover:underline"
                        >
                          {commit.shortSha}
                        </a>
                        <div className="mt-2 whitespace-pre-line break-words text-sm font-medium leading-6 text-slate-900">
                          {commit.message.split('\n')[0]}
                        </div>
                        <div className="mt-1 text-xs text-slate-500">
                          {commit.authorName}
                          {commit.authorEmail ? ` <${commit.authorEmail}>` : ''}
                        </div>
                      </div>
                      <div className="shrink-0 text-right text-xs text-slate-500">
                        {new Date(commit.committedAt).toLocaleString()}
                      </div>
                    </div>
                  </li>
                ))}
              </ul>
              <Pagination
                page={page}
                perPage={perPage}
                total={query.data.total}
                onPageChange={handlePageChange}
              />
            </>
          ) : null}
        </StateContainer>
      </section>
    </div>
  )
}
