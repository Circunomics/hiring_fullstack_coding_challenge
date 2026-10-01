import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'

interface LayoutProps {
  children: ReactNode
}

export function Layout({ children }: LayoutProps) {
  return (
    <div className="min-h-screen bg-[radial-gradient(ellipse_at_top,_#eaf2ff_0%,_#f6f8fb_42rem)] text-slate-900">
      <header className="sticky top-0 z-10 border-b border-white/70 bg-white/85 shadow-sm backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
          <Link to="/" className="flex items-center gap-3 rounded-lg font-semibold tracking-tight text-slate-950">
            <span className="grid h-9 w-9 place-items-center rounded-xl bg-blue-700 text-sm font-bold text-white shadow-sm">RI</span>
            <span>Repository Insights<span className="ml-2 hidden text-xs font-normal text-slate-500 sm:inline">Developer activity, at a glance</span></span>
          </Link>
          <nav className="text-sm font-medium text-slate-600">
            <Link to="/" className="rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-blue-800">
              Repositories
            </Link>
          </nav>
        </div>
      </header>
      <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">{children}</main>
    </div>
  )
}
