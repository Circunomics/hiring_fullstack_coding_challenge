import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { StateContainer } from './StateContainer'

describe('StateContainer', () => {
  it('renders the loading state with role="status"', () => {
    render(
      <StateContainer isLoading loadingText="Fetching">
        <div>children</div>
      </StateContainer>,
    )
    expect(screen.getByRole('status')).toHaveTextContent('Fetching')
    expect(screen.queryByText('children')).not.toBeInTheDocument()
  })

  it('renders the error state with role="alert"', () => {
    render(
      <StateContainer isLoading={false} error={new Error('boom')}>
        <div>children</div>
      </StateContainer>,
    )
    expect(screen.getByRole('alert')).toHaveTextContent('boom')
  })

  it('renders the empty state text when isEmpty is true', () => {
    render(
      <StateContainer isLoading={false} isEmpty emptyText="Nothing here">
        <div>children</div>
      </StateContainer>,
    )
    expect(screen.getByText('Nothing here')).toBeInTheDocument()
    expect(screen.queryByText('children')).not.toBeInTheDocument()
  })

  it('renders children on the happy path', () => {
    render(
      <StateContainer isLoading={false}>
        <div>children</div>
      </StateContainer>,
    )
    expect(screen.getByText('children')).toBeInTheDocument()
  })
})
