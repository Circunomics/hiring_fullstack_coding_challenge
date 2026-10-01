# Decisions

## Architecture

The backend follows a hexagonal architecture. The domain contains the main entities, value objects, business rules, and provider-independent contracts. Application use cases coordinate these pieces, while Symfony controllers, Doctrine, and the GitHub client stay at the edges.

This keeps the main logic independent from Symfony, the database, and GitHub, and makes the use cases easier to test. I kept the architecture fairly simple and only introduced abstractions where they were useful.

## Repository identity and duplicate commits

A repository is identified by its provider, owner, and name, with a database constraint enforcing uniqueness.

Within a repository, the commit SHA is also unique. The importer checks existing SHAs before inserting, and the database has the same constraint as a final safeguard. This makes repeated imports safe and prevents duplicate commits.

Contributors are currently identified globally using a normalized email address. When no usable email is available, the normalized author name is used with a no-email.local suffix.

This is a practical solution for the current GitHub-only scope, but it has limitations. Different emails can represent the same person, and two people sharing an email could be merged. A future version should use a stable provider account ID where possible and support identity reconciliation.

## Import and synchronization behavior

The importer currently fetches up to 1,000 recent commits and flushes them in batches of 100 to keep memory usage under control.

If an import fails partway through, already-saved commits remain in the database and the repository is marked as failed. A later import skips commits that are already stored, so it can safely continue without creating duplicates.

lastSyncedAt is only updated after a successful import. A failed import updates the status and error but leaves the previous successful sync time unchanged.

GitHub rate-limit errors are returned to the API and mark the sync as failed. There is currently no automatic retry or waiting for the reset time; the user can retry later.

## Contributor filters and dates

Search, date filtering, sorting, and pagination are handled by the database rather than in PHP.

The from date is inclusive. The to date is also inclusive from the user's point of view, but is converted to the start of the following day when querying the database. This prevents commits later on the selected end date from being missed.

Filters and pagination are kept in the URL so the current view can be shared and restored with browser navigation. Search is slightly delayed while typing to avoid making a request for every keystroke.

## Provider boundary

The application depends on a CommitProvider interface rather than directly on the GitHub client. Each provider maps its API response to the common RemoteCommit model, and a tagged-service registry selects the appropriate provider.

The current UI and validation are still GitHub-specific. Supporting another provider would therefore require its own adapter, validation, credentials, and UI changes, but the core import and contributor logic could remain unchanged.

## Deliberately out of scope

- GitLab and Bitbucket support
- Private repository authentication
- Background imports and automatic retries
- Import progress reporting
- Contributor identity reconciliation
- Large-scale performance optimization
- User accounts and authorization
- Production deployment and secret management

If I continued the project, I would first move imports to a background job with retries and progress reporting. After that, I would improve provider-specific identity handling and test the main queries with larger datasets.