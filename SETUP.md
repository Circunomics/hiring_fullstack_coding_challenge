# Local setup and development checks

## Requirements

- Docker with Docker Compose

## Start the application

From the repository root, build and start the stack:

```sh
docker compose up --build
```

Compose starts the Symfony backend, Vite frontend, and PostgreSQL databases. The backend applies migrations to both the development and test databases at startup. The frontend includes Tailwind CSS v4 and is available at <http://localhost:5175>; the Symfony backend is at <http://localhost:8001>. The development database is available at `localhost:5433` with database `app`, username `app`, and password `app`. In development, Symfony's Profiler is available from the backend at `/_profiler/`.

Stop the stack with `docker compose down`. The database data is stored in Docker volumes and remains after stopping the containers.

## Run backend tests

The test database is separate from development: database `app_test`, user `app`, password `app`. From the host it is available at `localhost:5434`. Inside Compose, the backend connects to the `test-database` service.

Run all backend tests inside the backend container:

```sh
docker compose exec backend php bin/phpunit
```

The API and integration tests create their own records inside DAMA-managed transactions and roll them back after each test, so no SQL fixture import is needed. Run one suite with:

```sh
docker compose exec backend composer test:unit
docker compose exec backend composer test:integration
docker compose exec backend composer test:api
```

## Static analysis and frontend checks

Run backend PHPStan, the Rector dry run, or the combined quality script:

```sh
docker compose exec backend composer stan
docker compose exec backend composer rector
docker compose exec backend composer quality
```

Run the frontend tests, production build, and lint checks:

```sh
docker compose exec frontend npm test
docker compose exec frontend npm run build
docker compose exec frontend npm run lint
```
