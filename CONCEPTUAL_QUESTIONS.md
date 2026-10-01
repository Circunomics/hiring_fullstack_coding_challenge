# Conceptual questions

## 1. How did you debug this project, and with which tools?

For the backend, I used the Symfony Profiler, especially for the slow endpoint requests. It helped me see where the time was spent and whether the problem was in the application code, database queries, or external HTTP calls. I also used Docker logs to investigate runtime and container issues.

For the frontend, I used the browser Inspector/DevTools and React Developer Tools to investigate network requests, component state, rendering issues, and API responses.

I also added PHPStan to the backend. It caught quite a few issues that were not immediately visible at runtime, which helped me improve the code while working on the project. I also used Rector, PHPUnit, ESLint, Vitest, and the Vite build to catch code quality and testing issues.

## 2. What is your approach to testing this project, and what did you choose to leave untested?

I focused the tests on the main behaviours that could easily regress: importing commits, handling duplicates, persistence, API responses, and the main frontend states such as loading, errors, filtering, and pagination.

The tests don't call GitHub directly. I use a stub  so the tests stay predictable and independent of GitHub.

I left things like GitHub rate limits, concurrent imports, and performance with very large repositories untested. These would be better covered by dedicated integration or performance tests with controlled data.

## 3. What would you change to support GitLab and Bitbucket?

I would add a CommitProvider implementation for each provider. Each implementation would handle the provider-specific API and map its response to the existing RemoteCommit DTO.

The main import flow, persistence, duplicate handling, and contributor aggregation could remain provider-independent.

The provider-specific parts, such as repository validation, authentication, and commit URLs, would need to be handled by each provider instead of assuming GitHub behaviour.

## 4. A user imports a repository with 500,000 commits and the contributors page becomes unusable. Where do you look first, and what are your options?

I would start with the Symfony Profiler (or Sentry if we have it) and the database query plan for the contributors endpoint.

I'd look at the grouping, filtering, and count queries and use EXPLAIN ANALYZE with representative data to see where the actual bottleneck is.

Depending on the results, possible improvements would be better indexes, PostgreSQL trigram search for the contributor search, precomputed contributor statistics, or cursor-based pagination.

## 5. Which part of your solution would you not ship to production as-is, and why?

It currently makes GitHub requests and imports commits while the user is waiting for the request to finish. With a large repository, this can lead to long requests, timeouts, and problems with GitHub rate limits.

For production, I would move the import to a background job, add retries for temporary failures, and expose the import status and progress to the user.

I would also move secrets to proper production secret management and add authentication/authorization before exposing the application to real users.