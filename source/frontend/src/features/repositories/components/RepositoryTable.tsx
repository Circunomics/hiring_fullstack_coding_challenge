import { Link } from 'react-router-dom'
import type { Repository } from '../types'
import { useSyncRepository } from '../api/repositoriesApi'

interface Props {
  repositories: Repository[]
}

function formatDate(iso: string | null): string {
  if (iso === null) return '—'
  const d = new Date(iso)
  return `${d.toLocaleDateString()} ${d.toLocaleTimeString()}`
}

function StatusBadge({ status }: { status: Repository['syncStatus'] }) {
  const styles: Record<Repository['syncStatus'], string> = {
    pending: 'bg-slate-100 text-slate-700 ring-slate-200',
    running: 'bg-blue-50 text-blue-700 ring-blue-200',
    success: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    failed: 'bg-red-50 text-red-700 ring-red-200',
  }
  return <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset ${styles[status]}`}>
    {status === 'running' ? <span aria-hidden="true" className="h-1.5 w-1.5 animate-pulse rounded-full bg-current" /> : null}
    {status}
  </span>
}

export function RepositoryTable({ repositories }: Props) {
  const syncMutation = useSyncRepository()

  return (
    <div className="overflow-x-auto">
    <table className="w-full min-w-[680px] border-collapse text-sm">
      <thead>
        <tr className="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
          <th className="py-3 pr-3 font-semibold">Repository</th>
          <th className="py-3 pr-3 font-semibold">Commits</th>
          <th className="py-3 pr-3 font-semibold">Status</th>
          <th className="py-3 pr-3 font-semibold">Last synced</th>
          <th className="py-3 text-right font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
        {repositories.map((repo) => (
          <tr key={repo.id} className="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
            <td className="py-4 pr-3">
              <Link to={`/repositories/${repo.id}`} className="font-semibold text-slate-900 decoration-blue-500 underline-offset-4 hover:text-blue-800 hover:underline">
                {repo.fullName}
              </Link>
              {repo.lastSyncError ? (
                <div className="mt-1 text-xs text-red-700" title={repo.lastSyncError}>
                  Last error: {repo.lastSyncError.slice(0, 80)}
                  {repo.lastSyncError.length > 80 ? '…' : ''}
                </div>
              ) : null}
            </td>
            <td className="py-4 pr-3 font-medium tabular-nums text-slate-700">{repo.commitCount.toLocaleString()}</td>
            <td className="py-2 pr-3">
              <StatusBadge status={repo.syncStatus} />
            </td>
            <td className="py-4 pr-3 text-slate-600">{formatDate(repo.lastSyncedAt)}</td>
            <td className="py-4 text-right">
              <button
                type="button"
                onClick={() => syncMutation.mutate(repo.id)}
                disabled={syncMutation.isPending && syncMutation.variables === repo.id}
                className="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800 disabled:cursor-wait disabled:opacity-50"
              >
                {syncMutation.isPending && syncMutation.variables === repo.id ? 'Syncing…' : 'Re-sync'}
              </button>
              {syncMutation.isError && syncMutation.variables === repo.id ? (
                <div role="alert" className="mt-1 max-w-56 text-left text-xs text-red-700">
                  {syncMutation.error instanceof Error ? syncMutation.error.message : 'Re-sync failed.'}
                </div>
              ) : null}
            </td>
          </tr>
        ))}
      </tbody>
    </table>
    </div>
  )
}
