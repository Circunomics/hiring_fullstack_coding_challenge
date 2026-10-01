function App() {
  return (
    <main className="min-h-screen bg-slate-50 px-6 py-16 text-slate-900 sm:py-24">
      <div className="mx-auto max-w-3xl">
        <p className="text-sm font-semibold uppercase tracking-widest text-indigo-700">
          Project setup
        </p>
        <h1 className="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">
          React and Tailwind are ready
        </h1>
        <p className="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
          The frontend scaffold is running with Vite, React, TypeScript, and Tailwind CSS.
          Feature screens can be added in the next step.
        </p>

        <section className="mt-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div>
              <h2 className="text-lg font-semibold">Frontend scaffold</h2>
              <p className="mt-1 text-sm text-slate-600">
                Tailwind utility classes are compiling through the Vite plugin.
              </p>
            </div>
            <span className="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
              <span aria-hidden="true" className="h-2 w-2 rounded-full bg-emerald-500" />
              Ready
            </span>
          </div>
        </section>
      </div>
    </main>
  )
}

export default App
