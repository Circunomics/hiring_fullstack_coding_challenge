import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { Layout } from '@/shared/components/Layout'
import { RepositoriesPage } from '@/features/repositories/pages/RepositoriesPage'
import { ContributorsPage } from '@/features/contributors/pages/ContributorsPage'
import { ContributorCommitsPage } from '@/features/contributors/pages/ContributorCommitsPage'

export function AppRouter() {
  return (
    <BrowserRouter>
      <Layout>
        <Routes>
          <Route path="/" element={<RepositoriesPage />} />
          <Route path="/repositories/:repositoryId" element={<ContributorsPage />} />
          <Route
            path="/repositories/:repositoryId/contributors/:contributorId"
            element={<ContributorCommitsPage />}
          />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </Layout>
    </BrowserRouter>
  )
}
