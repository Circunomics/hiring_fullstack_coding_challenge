# AI usage

I used Claude Code during implementation and OpenAI Codex for additional implementation, debugging, and documentation support. I did not use separate AI agents.

I made the product and architecture decisions, wrote and reviewed code, set constraints for the implementation, and tested the app. The assistants helped with focused implementation, troubleshooting build and test failures, and reviewing and documenting the changes. I steered the work toward Symfony use cases and DTOs, a separate controller for each endpoint, a dedicated PostgreSQL test database with transaction rollback, and a feature-oriented React frontend.

One generated design I rejected was a single controller class that grouped several API endpoints. I asked for one controller per endpoint because that matches the structure I find easier to navigate and maintain in Symfony projects.
