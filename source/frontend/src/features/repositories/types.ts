export type SyncStatus = 'pending' | 'running' | 'success' | 'failed'

export interface Repository {
  id: number
  provider: string
  owner: string
  name: string
  fullName: string
  commitCount: number
  syncStatus: SyncStatus
  lastSyncedAt: string | null
  lastSyncError: string | null
}

export interface ImportResult {
  repositoryId: number
  seen: number
  inserted: number
  skippedDuplicates: number
}
