import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, type RenderOptions } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import type { ReactElement, ReactNode } from 'react'

interface Options extends Omit<RenderOptions, 'wrapper'> {
  initialRoute?: string
}

/**
 * Renders a component inside a fresh QueryClient + MemoryRouter, so tests can
 * exercise pages that use react-query and react-router without sharing state
 * between test cases.
 */
export function renderWithProviders(ui: ReactElement, options: Options = {}) {
  const client = new QueryClient({
    defaultOptions: {
      queries: { retry: false, gcTime: 0, staleTime: 0 },
      mutations: { retry: false },
    },
  })

  const Wrapper = ({ children }: { children: ReactNode }) => (
    <QueryClientProvider client={client}>
      <MemoryRouter initialEntries={[options.initialRoute ?? '/']}>{children}</MemoryRouter>
    </QueryClientProvider>
  )

  return { ...render(ui, { wrapper: Wrapper, ...options }), queryClient: client }
}
