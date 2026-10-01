# Local setup

## Requirements

- Docker with Docker Compose

## Start the application

From the repository root, run:

```sh
docker compose up
```

Compose builds and starts the Symfony backend, Vite frontend, and PostgreSQL database. The frontend includes Tailwind CSS v4 and is available at <http://localhost:5175>. The Symfony backend listens at <http://localhost:8001>.