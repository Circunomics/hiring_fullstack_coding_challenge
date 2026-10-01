import { act, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { renderWithProviders } from '@/test/renderWithQuery'
import { AddRepositoryForm } from './AddRepositoryForm'

describe('AddRepositoryForm', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })
  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('requires exactly an owner and repository name', async () => {
    renderWithProviders(<AddRepositoryForm />)
    const user = userEvent.setup()
    const button = screen.getByRole('button', { name: /import/i })

    expect(button).toBeDisabled()

    await user.type(screen.getByRole('textbox'), 'no-slash-here')
    expect(button).toBeDisabled()

    await user.clear(screen.getByRole('textbox'))
    await user.type(screen.getByRole('textbox'), 'symfony/symfony')
    expect(button).toBeEnabled()

    await user.type(screen.getByRole('textbox'), '/extra')
    expect(button).toBeDisabled()
  })

  it('POSTs the slug and clears the input on success', async () => {
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce(
      new Response(
        JSON.stringify({ repositoryId: 1, seen: 5, inserted: 5, skippedDuplicates: 0 }),
        { status: 201, headers: { 'content-type': 'application/json' } },
      ),
    )

    renderWithProviders(<AddRepositoryForm />)
    const user = userEvent.setup()

    const input = screen.getByRole('textbox')
    await user.type(input, 'symfony/symfony')
    await user.click(screen.getByRole('button', { name: /import/i }))

    await waitFor(() => {
      expect(input).toHaveValue('')
    })

    const call = (globalThis.fetch as ReturnType<typeof vi.fn>).mock.calls[0]
    expect(call[0]).toBe('/api/v1/repositories')
    expect(call[1]?.method).toBe('POST')
    expect(JSON.parse(call[1]?.body)).toEqual({ provider: 'github', slug: 'symfony/symfony' })
  })

  it('shows a progress message while the import request is pending', async () => {
    let finishRequest: ((response: Response) => void) | undefined
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockImplementationOnce(
      () => new Promise<Response>((resolve) => { finishRequest = resolve }),
    )

    renderWithProviders(<AddRepositoryForm />)
    const user = userEvent.setup()
    await user.type(screen.getByRole('textbox'), 'symfony/symfony')
    await user.click(screen.getByRole('button', { name: /import repository/i }))

    expect(screen.getByRole('status')).toHaveTextContent(/fetching the latest commits/i)
    expect(screen.getByRole('button', { name: /importing/i })).toBeDisabled()

    await act(async () => {
      finishRequest?.(new Response(JSON.stringify({ repositoryId: 1, seen: 0, inserted: 0, skippedDuplicates: 0 }), {
        status: 201,
        headers: { 'content-type': 'application/json' },
      }))
    })
  })

  it('surfaces the API error message on failure', async () => {
    ;(globalThis.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce(
      new Response(JSON.stringify({ error: { code: 'bad_request', message: 'Invalid slug' } }), {
        status: 400,
        headers: { 'content-type': 'application/json' },
      }),
    )

    renderWithProviders(<AddRepositoryForm />)
    const user = userEvent.setup()

    await user.type(screen.getByRole('textbox'), 'unknown/repository')
    await user.click(screen.getByRole('button', { name: /import/i }))

    await waitFor(() => {
      expect(screen.getByRole('alert')).toHaveTextContent('Invalid slug')
    })
  })
})
