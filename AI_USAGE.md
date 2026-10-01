# AI usage

I used Claude Code as coding assistant during development. I made the architectural and implementation decisions, wrote and reviewed the code, and used AI mainly to speed up some parts, troubleshoot errors, and review parts of the implementation. I did not use separate AI agents.

I made the product and architecture decisions, set constraints for the implementation, and reviewed the generated changes. I steered the work toward Symfony use cases and DTOs, a separate controller for each endpoint, a dedicated PostgreSQL test database with transaction rollback, and a feature-oriented React frontend.

One generated design I rejected was a single controller class that grouped several API endpoints. I asked for one controller per endpoint because that matches the structure I find easier to navigate and maintain in Symfony projects.
