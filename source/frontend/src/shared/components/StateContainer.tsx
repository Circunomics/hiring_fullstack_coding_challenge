import type { ReactNode } from 'react'

interface StateContainerProps {
  isLoading: boolean
  error?: unknown
  isEmpty?: boolean
  emptyText?: string
  loadingText?: string
  children: ReactNode
}

/**
 * Small helper that renders one of the four canonical states — loading, error,
 * empty, or content — so every page enforces them consistently.
 */
export function StateContainer({
  isLoading,
  error,
  isEmpty,
  emptyText = 'Nothing to show yet.',
  loadingText = 'Loading…',
  children,
}: StateContainerProps) {
  if (isLoading) {
    return (
      <div role="status" className="flex items-center justify-center gap-3 py-10 text-sm font-medium text-slate-500">
        <span aria-hidden="true" className="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-blue-600" />
        {loadingText}
      </div>
    )
  }

  if (error) {
    const message = error instanceof Error ? error.message : 'Something went wrong.'
    return (
      <div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        {message}
      </div>
    )
  }

  if (isEmpty) {
    return <div className="py-12 text-center text-sm text-slate-500">{emptyText}</div>
  }

  return <>{children}</>
}
