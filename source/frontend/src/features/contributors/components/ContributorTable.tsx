import { Link } from 'react-router-dom'
import type { Contributor } from '../types'

interface Props {
  repositoryId: number
  contributors: Contributor[]
}

export function ContributorTable({ repositoryId, contributors }: Props) {
  return (
    <div className="overflow-x-auto">
    <table className="w-full min-w-[580px] border-collapse text-sm">
      <thead>
        <tr className="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
          <th className="py-3 pr-3 font-semibold">Contributor</th>
          <th className="py-3 pr-3 font-semibold">Commits</th>
          <th className="py-3 pr-3 font-semibold">Last commit</th>
        </tr>
      </thead>
      <tbody>
        {contributors.map((contributor) => (
          <tr key={contributor.id} className="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
            <td className="py-4 pr-3">
              <div className="flex items-center gap-3">
                {contributor.avatarUrl ? (
                    <img src={contributor.avatarUrl} alt="" width={36} height={36} className="rounded-full bg-slate-100 ring-1 ring-slate-200" loading="lazy" />
                ) : (
                  <div className="grid h-9 w-9 place-items-center rounded-full bg-blue-100 text-xs font-bold uppercase text-blue-700" aria-hidden="true">{contributor.displayName.slice(0, 1)}</div>
                )}
                <div>
                  <Link
                    to={`/repositories/${repositoryId}/contributors/${contributor.id}`}
                    className="font-semibold text-slate-900 decoration-blue-500 underline-offset-4 hover:text-blue-800 hover:underline"
                  >
                    {contributor.displayName}
                  </Link>
                  {contributor.email ? (
                    <div className="text-xs text-slate-500">{contributor.email}</div>
                  ) : null}
                </div>
              </div>
            </td>
            <td className="py-4 pr-3 font-medium tabular-nums text-slate-700">{contributor.commitCount.toLocaleString()}</td>
            <td className="py-4 pr-3 text-slate-600">
              {new Date(contributor.lastCommittedAt).toLocaleDateString()}
            </td>
          </tr>
        ))}
      </tbody>
    </table>
    </div>
  )
}
