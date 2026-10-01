import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { renderWithProviders } from '@/test/renderWithQuery'
import { RepositoriesPage } from './RepositoriesPage'

describe('RepositoriesPage', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('renders the loading state, then the empty state when the API returns no items', async () => {
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce(jsonResponse({ items: [] }))

    renderWithProviders(<RepositoriesPage />)

    expect(screen.getByRole('status')).toHaveTextContent(/loading repositories/i)

    await waitFor(() => {
      expect(screen.getByText(/no repositories yet/i)).toBeInTheDocument()
    })
  })

  it('renders repositories returned by the API', async () => {
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce(
      jsonResponse({
        items: [
          {
            id: 1,
            provider: 'github',
            owner: 'symfony',
            name: 'symfony',
            fullName: 'symfony/symfony',
            commitCount: 42,
            syncStatus: 'success',
            lastSyncedAt: '2026-01-15T12:00:00Z',
            lastSyncError: null,
          },
        ],
      }),
    )

    renderWithProviders(<RepositoriesPage />)

    expect(await screen.findByRole('link', { name: 'symfony/symfony' })).toBeInTheDocument()
    expect(await screen.findByText('42')).toBeInTheDocument()
    expect(screen.getByText('success')).toBeInTheDocument()
  })

  it('refreshes the repository list after a failed import', async () => {
    let listRequests = 0
    const fetchMock = globalThis.fetch as ReturnType<typeof vi.fn>
    fetchMock.mockImplementation((_url: unknown, init?: RequestInit) => {
      if (init?.method === 'POST') {
        return Promise.resolve(
          new Response(JSON.stringify({ error: { code: 'sync_failed', message: 'Import failed' } }), {
            status: 502,
            headers: { 'content-type': 'application/json' },
          }),
        )
      }

      listRequests += 1
      return Promise.resolve(jsonResponse({
        items: listRequests === 1 ? [] : [{
          id: 2,
          provider: 'github',
          owner: 'acme',
          name: 'repo',
          fullName: 'acme/repo',
          commitCount: 0,
          syncStatus: 'failed',
          lastSyncedAt: null,
          lastSyncError: 'Import failed',
        }],
      }))
    })

    renderWithProviders(<RepositoriesPage />)
    await screen.findByText(/no repositories yet/i)
    const user = userEvent.setup()
    await user.type(screen.getByRole('textbox'), 'acme/repo')
    await user.click(screen.getByRole('button', { name: /import repository/i }))

    expect(await screen.findByRole('alert')).toHaveTextContent('Import failed')
    expect(await screen.findByRole('link', { name: 'acme/repo' })).toBeInTheDocument()
    expect(screen.getByText('failed')).toBeInTheDocument()
    expect(listRequests).toBe(2)
  })

  it('renders the error state when the API rejects', async () => {
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce(
      new Response(JSON.stringify({ error: { code: 'internal_error', message: 'kaboom' } }), {
        status: 500,
        headers: { 'content-type': 'application/json' },
      }),
    )

    renderWithProviders(<RepositoriesPage />)

    await waitFor(() => {
      expect(screen.getByRole('alert')).toHaveTextContent('kaboom')
    })
  })
})

function jsonResponse(body: unknown): Response {
  return new Response(JSON.stringify(body), {
    status: 200,
    headers: { 'content-type': 'application/json' },
  })
}
