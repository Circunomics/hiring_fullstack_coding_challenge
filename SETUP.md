# Local setup

## Requirements

- Docker with Docker Compose

## Start the application

From the repository root, run:

```sh
docker compose up
```

Compose builds and starts the Symfony backend, Vite frontend, and PostgreSQL database. The frontend includes Tailwind CSS v4 and is available at <http://localhost:5175>. The Symfony backend listens at <http://localhost:8001>.

## Run backend tests

The test suite uses a separate PostgreSQL container and database. It starts with the regular Compose stack, and the backend applies migrations to both the development and test databases during startup:

```sh
docker compose up -d --build
```

Test database credentials are `app` / `app`; connect from the host at `localhost:5434` to database `app_test`.

Run all backend tests inside the backend container:

```sh
docker compose exec backend php bin/phpunit
```

The API and integration tests create their own records inside DAMA-managed transactions and roll them back after each test, so no SQL fixture import is needed. Run one suite with `docker compose exec backend php bin/phpunit --testsuite=unit`, `--testsuite=integration`, or `--testsuite=api`.
