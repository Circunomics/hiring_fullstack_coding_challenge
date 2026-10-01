import { totalPages } from '@/shared/api/pagination'

interface PaginationProps {
  page: number
  perPage: number
  total: number
  onPageChange: (page: number) => void
}

export function Pagination({ page, perPage, total, onPageChange }: PaginationProps) {
  const pages = totalPages(total, perPage)
  const canPrev = page > 1
  const canNext = page < pages

  return (
    <nav className="flex items-center justify-between gap-4 py-3 text-sm text-slate-600">
      <span>
        Page <strong>{page}</strong> of <strong>{pages}</strong> — {total.toLocaleString()} total
      </span>
      <div className="flex gap-2">
        <button
          type="button"
          className="rounded border border-slate-300 px-3 py-1 disabled:opacity-40"
          disabled={!canPrev}
          onClick={() => onPageChange(page - 1)}
        >
          Previous
        </button>
        <button
          type="button"
          className="rounded border border-slate-300 px-3 py-1 disabled:opacity-40"
          disabled={!canNext}
          onClick={() => onPageChange(page + 1)}
        >
          Next
        </button>
      </div>
    </nav>
  )
}
