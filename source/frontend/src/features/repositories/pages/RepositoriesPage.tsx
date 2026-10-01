import { useRepositories } from '../api/repositoriesApi'
import { AddRepositoryForm } from '../components/AddRepositoryForm'
import { RepositoryTable } from '../components/RepositoryTable'
import { StateContainer } from '@/shared/components/StateContainer'

export function RepositoriesPage() {
  const query = useRepositories()

  return (
    <div className="space-y-7">
      <section className="space-y-2">
        <p className="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">Workspace</p>
        <h1 className="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Repositories</h1>
        <p className="max-w-2xl text-sm leading-6 text-slate-600">
          Import a GitHub repository below. We'll fetch its most recent 1000 commits. Re-import is idempotent.
        </p>
      </section>

      <AddRepositoryForm />

      <section className="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-[0_12px_40px_-25px_rgba(15,23,42,0.35)] sm:p-6">
        <div className="mb-4 flex flex-wrap items-end justify-between gap-2">
          <div>
            <h2 className="text-lg font-semibold tracking-tight text-slate-900">Tracked repositories</h2>
            <p className="mt-1 text-sm text-slate-500">Import, inspect, and refresh commit history.</p>
          </div>
        </div>
        <StateContainer
          isLoading={query.isLoading}
          error={query.error}
          isEmpty={query.data?.length === 0}
          emptyText="No repositories yet. Import one above to get started."
          loadingText="Loading repositories…"
        >
          {query.data ? <RepositoryTable repositories={query.data} /> : null}
        </StateContainer>
      </section>
    </div>
  )
}
