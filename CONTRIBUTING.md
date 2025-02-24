# Contributing to This Project

Thank you for your interest in contributing! This guide will help you get started.

## Code of Conduct

By participating in this project, you agree to follow our Code of Conduct. Please be respectful and constructive in all interactions.

## Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/OBMS-Open-Business-Management-Software/core.git
   ```

2. Install Dependencies:
   ```bash
   composer install
   npm install
   ```

3. Configure Environment:
   ```bash
   cp .env.example .env
   ```

4. Start the Docker containers:
   ```bash
   ./vendor/bin/sail up -d
   ```

5. Generate required keys:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan passport:keys
   ```

6. Migrate the database:
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

## How to Contribute

### Reporting Issues

- Before creating an issue, please check if a similar issue already exists
- Use the issue template when available
- Provide as much detail as possible, including:
  - Steps to reproduce the issue
  - Expected behavior
  - Actual behavior
  - Error messages and screenshots if applicable
  - Your environment details (Docker version, OS, etc.)

### Submitting Pull Requests

1. Fork the repository
2. Create a new branch from `main`:
   ```bash
   git checkout -b feat/your-feature-name
   ```
3. Make your changes
4. Write or update tests as needed
5. Ensure all tests pass
6. Commit your changes using clear commit messages
7. Push to your fork
8. Create a Pull Request

#### Pull Request Guidelines

- Follow the existing code style and conventions
- Include tests for new features
- Update documentation as needed
- One pull request per feature/fix
- Keep pull requests focused and small

### Commit Message Guidelines

We follow [Conventional Commits](https://www.conventionalcommits.org/). Use these types:

- `feat:` - New features
- `fix:` - Bug fixes
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `refactor:` - Code refactoring
- `test:` - Adding or modifying tests
- `chore:` - Maintenance tasks

Example:
```bash
git commit -m "feat: add new authentication feature"
```

## Docker Development Notes

- The development environment uses Laravel Sail for Docker containerization
- By default, Laravel Sail containers are used for database and Redis cache
- External database and Redis connections can be configured if needed
- Do not use development containers in production

## Code Review Process

1. Maintainers will review your PR
2. Address any requested changes
3. Once approved, your PR will be merged

## Questions?

If you have questions, feel free to open an issue or contact the maintainers.

Thank you for contributing! 🎉 