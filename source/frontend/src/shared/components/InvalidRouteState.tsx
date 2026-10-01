import { Link } from 'react-router-dom'

interface InvalidRouteStateProps {
  title: string
  message: string
  backTo: string
  backLabel: string
}

export function InvalidRouteState({ title, message, backTo, backLabel }: InvalidRouteStateProps) {
  return (
    <section className="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-sm">
      <h1 className="text-xl font-semibold text-slate-900">{title}</h1>
      <p className="mt-2 text-sm text-slate-600">{message}</p>
      <Link to={backTo} className="mt-5 inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
        {backLabel}
      </Link>
    </section>
  )
}
