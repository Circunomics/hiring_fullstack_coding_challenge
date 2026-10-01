import { useState } from 'react'
import { useImportRepository } from '../api/repositoriesApi'

export function AddRepositoryForm() {
  const [slug, setSlug] = useState('')
  const importMutation = useImportRepository()
  const normalizedSlug = slug.trim()
  const isValidSlug = /^[^/\s]+\/[^/\s]+$/.test(normalizedSlug)

  const submit = (event: React.FormEvent) => {
    event.preventDefault()
    if (!isValidSlug) return
    importMutation.mutate(
      { provider: 'github', slug: normalizedSlug },
      {
        onSuccess: () => setSlug(''),
      },
    )
  }

  return (
    <form
      onSubmit={submit}
      aria-busy={importMutation.isPending}
      className="flex flex-col gap-4 rounded-2xl border border-blue-100 bg-white p-4 shadow-[0_16px_48px_-32px_rgba(37,99,235,0.5)] sm:flex-row sm:flex-wrap sm:items-end sm:p-5"
    >
      <label className="min-w-0 flex-1 text-sm">
        <span className="mb-1.5 block font-semibold text-slate-800">Add a GitHub repository</span>
        <input
          aria-describedby={importMutation.error ? 'repository-help repository-error' : 'repository-help'}
          type="text"
          value={slug}
          onChange={(event) => setSlug(event.target.value)}
          placeholder="owner/repo (e.g. symfony/symfony)"
          className="w-full rounded-xl border border-slate-300 bg-slate-50/60 px-3.5 py-3 transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none"
          disabled={importMutation.isPending}
        />
      </label>
      <button
        type="submit"
        disabled={!isValidSlug || importMutation.isPending}
        className="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus-visible:outline-blue-500 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none"
      >
        {importMutation.isPending ? (
          <>
            <span aria-hidden="true" className="h-4 w-4 animate-spin rounded-full border-2 border-blue-200 border-t-white" />
            Importing
          </>
        ) : 'Import repository'}
      </button>
      <p id="repository-help" className="text-xs text-slate-500 sm:basis-full">
        Enter the owner and repository name, for example <span className="font-mono">symfony/symfony</span>.
      </p>
      {importMutation.isPending ? (
        <div role="status" aria-live="polite" className="flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-800 sm:basis-full">
          <span>Fetching the latest commits. This can take a little while for large repositories.</span>
        </div>
      ) : null}
      {importMutation.error ? (
        <span id="repository-error" role="alert" className="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800 sm:basis-full">
          {importMutation.error instanceof Error ? importMutation.error.message : 'Import failed.'}
        </span>
      ) : null}
      {importMutation.isSuccess ? (
        <p role="status" className="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800 sm:basis-full">
          Import complete: {importMutation.data.inserted.toLocaleString()} new commits added, {importMutation.data.skippedDuplicates.toLocaleString()} duplicates skipped.
        </p>
      ) : null}
    </form>
  )
}
