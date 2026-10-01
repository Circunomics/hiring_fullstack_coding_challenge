import { useCallback, useEffect, useState } from 'react'
import { useLocation } from 'react-router-dom'
import type { ContributorFilters, ContributorSort, SortDirection } from '../types'

interface Props {
  filters: ContributorFilters
  onChange: (patch: Partial<ContributorFilters>) => void
}

const SORT_OPTIONS: { value: ContributorSort; label: string }[] = [
  { value: 'commits', label: 'Commit count' },
  { value: 'name', label: 'Name' },
  { value: 'lastCommittedAt', label: 'Last commit' },
]

export function ContributorFiltersBar({ filters, onChange }: Props) {
  const location = useLocation()
  const onSearch = useCallback((search: string) => onChange({ search, page: 1 }), [onChange])

  return (
    <div className="grid grid-cols-1 gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-6 lg:p-5">
      <label className="col-span-2 text-sm">
        <span className="mb-1 block font-medium text-slate-700">Search</span>
        <DebouncedSearchInput
          key={location.key}
          value={filters.search}
          onSearch={onSearch}
        />
      </label>
      <label className="text-sm">
        <span className="mb-1 block font-medium text-slate-700">From</span>
        <input
          type="date"
          value={filters.from ?? ''}
          onChange={(event) => onChange({ from: event.target.value === '' ? null : event.target.value, page: 1 })}
          className="w-full rounded-lg border border-slate-300 bg-slate-50/60 px-3 py-2.5 text-sm transition focus:border-blue-500 focus:bg-white focus:outline-none"
        />
      </label>
      <label className="text-sm">
        <span className="mb-1 block font-medium text-slate-700">To</span>
        <input
          type="date"
          value={filters.to ?? ''}
          onChange={(event) => onChange({ to: event.target.value === '' ? null : event.target.value, page: 1 })}
          className="w-full rounded-lg border border-slate-300 bg-slate-50/60 px-3 py-2.5 text-sm transition focus:border-blue-500 focus:bg-white focus:outline-none"
        />
      </label>
      <label className="text-sm">
        <span className="mb-1 block font-medium text-slate-700">Sort</span>
        <select
          value={filters.sort}
          onChange={(event) => onChange({ sort: event.target.value as ContributorSort, page: 1 })}
          className="w-full rounded-lg border border-slate-300 bg-slate-50/60 px-3 py-2.5 text-sm transition focus:border-blue-500 focus:bg-white focus:outline-none"
        >
          {SORT_OPTIONS.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      </label>
      <label className="text-sm">
        <span className="mb-1 block font-medium text-slate-700">Direction</span>
        <select
          value={filters.direction}
          onChange={(event) => onChange({ direction: event.target.value as SortDirection, page: 1 })}
          className="w-full rounded-lg border border-slate-300 bg-slate-50/60 px-3 py-2.5 text-sm transition focus:border-blue-500 focus:bg-white focus:outline-none"
        >
          <option value="desc">Descending</option>
          <option value="asc">Ascending</option>
        </select>
      </label>
    </div>
  )
}

interface DebouncedSearchInputProps {
  value: string
  onSearch: (value: string) => void
}

function DebouncedSearchInput({ value, onSearch }: DebouncedSearchInputProps) {
  const [draft, setDraft] = useState(value)

  useEffect(() => {
    if (draft === value) return
    const timeout = window.setTimeout(() => onSearch(draft), 300)
    return () => window.clearTimeout(timeout)
  }, [draft, onSearch, value])

  return (
    <input
      type="text"
      value={draft}
      onChange={(event) => setDraft(event.target.value)}
      placeholder="name or email"
      className="w-full rounded-lg border border-slate-300 bg-slate-50/60 px-3 py-2.5 text-sm transition focus:border-blue-500 focus:bg-white focus:outline-none"
    />
  )
}
