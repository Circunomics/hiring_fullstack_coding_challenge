import { screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { Route, Routes, useSearchParams } from 'react-router-dom'
import { renderWithProviders } from '@/test/renderWithQuery'
import { ContributorsPage } from './ContributorsPage'
import { ContributorCommitsPage } from './ContributorCommitsPage'

describe('contributor routes', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn().mockResolvedValue(jsonResponse({ items: [], total: 0, page: 1, perPage: 20 })))
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('restores contributor filters from the URL and debounces search requests', async () => {
    renderAt(
      '/repositories/12?search=alice&page=2&sort=name&direction=asc&from=2026-01-01',
    )

    const search = screen.getByRole('textbox', { name: 'Search' })
    expect(search).toHaveValue('alice')
    await screen.findByText('No contributors match these filters.')

    const fetchMock = globalThis.fetch as ReturnType<typeof vi.fn>
    expect(requestUrl(fetchMock.mock.calls[0][0]).searchParams.get('page')).toBe('2')
    expect(requestUrl(fetchMock.mock.calls[0][0]).searchParams.get('from')).toBe('2026-01-01')

    const user = userEvent.setup()
    await user.clear(search)
    await user.type(search, 'bob')

    await waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(2), { timeout: 1500 })
    const updatedUrl = requestUrl(fetchMock.mock.calls[1][0])
    expect(updatedUrl.searchParams.get('search')).toBe('bob')
    expect(updatedUrl.searchParams.get('page')).toBe('1')
    const routeParams = new URLSearchParams(screen.getByTestId('route-search').textContent ?? '')
    expect(routeParams.get('search')).toBe('bob')
    expect(routeParams.get('page')).toBeNull()
  })

  it.each([
    ['/repositories/not-a-number', 'Repository not found'],
    ['/repositories/12/contributors/not-a-number/commits', 'Contributor not found'],
  ])('shows an invalid route state without requesting data for malformed IDs', (route, title) => {
    renderAt(route)

    expect(screen.getByRole('heading', { name: title })).toBeInTheDocument()
    expect(globalThis.fetch).not.toHaveBeenCalled()
  })

  it('restores commit pagination from the URL and updates it when paging', async () => {
    const commit = {
      id: 8,
      sha: 'abcdef123456',
      shortSha: 'abcdef1',
      message: 'Fix issue',
      committedAt: '2026-01-15T12:00:00Z',
      htmlUrl: 'https://github.com/acme/repo/commit/abcdef123456',
      authorName: 'Alex Doe',
      authorEmail: 'alex@example.com',
    }
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockResolvedValue(
      jsonResponse({ items: [commit], total: 61, page: 3, perPage: 20 }),
    )

    renderAt('/repositories/12/contributors/7/commits?page=3')

    expect(await screen.findByText('Fix issue')).toBeInTheDocument()
    const fetchMock = globalThis.fetch as ReturnType<typeof vi.fn>
    expect(requestUrl(fetchMock.mock.calls[0][0]).searchParams.get('page')).toBe('3')

    await userEvent.setup().click(screen.getByRole('button', { name: 'Previous' }))
    await waitFor(() => expect(fetchMock).toHaveBeenCalledTimes(2))
    expect(requestUrl(fetchMock.mock.calls[1][0]).searchParams.get('page')).toBe('2')
  })
})

function renderAt(initialRoute: string) {
  return renderWithProviders(<RoutedTestApp />, { initialRoute })
}

function RoutedTestApp() {
  return (
    <>
      <Routes>
      <Route path="/repositories/:repositoryId" element={<ContributorsPage />} />
      <Route
        path="/repositories/:repositoryId/contributors/:contributorId/commits"
        element={<ContributorCommitsPage />}
      />
      </Routes>
      <CurrentRouteSearch />
    </>
  )
}

function CurrentRouteSearch() {
  const [searchParams] = useSearchParams()
  return <output data-testid="route-search">{searchParams.toString()}</output>
}

function requestUrl(request: unknown): URL {
  return new URL(String(request), 'http://localhost')
}

function jsonResponse(body: unknown): Response {
  return new Response(JSON.stringify(body), {
    status: 200,
    headers: { 'content-type': 'application/json' },
  })
}
