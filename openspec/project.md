# Project Context

## Purpose
A Symfony-based web application following Domain Driven Design (DDD) principles, built with modern PHP practices and comprehensive testing. The project emphasizes clean architecture, SOLID principles, and maintainable code structure.

## Tech Stack
- **PHP**: >=8.4 (with strict types enabled)
- **Framework**: Symfony 7.3
- **Package Manager**: Composer
- **Architecture**: Domain Driven Design (DDD) with CQRS pattern
- **Coding Standards**: PSR-12
- **Testing**: PHPUnit (Unit and Feature tests)

### Core Symfony Components
- `symfony/console`: Command-line interface
- `symfony/dotenv`: Environment variable management
- `symfony/framework-bundle`: Core framework bundle
- `symfony/runtime`: Runtime component
- `symfony/yaml`: YAML configuration parsing

## Project Conventions

### Code Style
- **Standard**: PSR-12 Extended Coding Style Guide
- **Type Safety**: `declare(strict_types=1);` required in all PHP files
- **Type Hints**: All parameters and return types must be declared
- **Immutability**: Use `readonly` classes and properties where appropriate (PHP 8.4)
- **Indentation**: 4 spaces (no tabs)
- **Braces**: Opening braces on same line for classes/methods, new line for control structures
- **Naming**:
  - Classes: `PascalCase`
  - Methods: `camelCase`
  - Properties: `camelCase`
  - Constants: `UPPER_SNAKE_CASE`
- **Line Length**: Maximum 120 characters (break at logical points)
- **Early Returns**: Prefer early returns over nested if statements

### Architecture Patterns

#### Domain Driven Design (DDD)
The project follows a strict DDD structure:

```
src/
├── Domain/              # Core business logic
│   └── {BoundedContext}/
│       ├── Entity/      # Aggregate roots and entities
│       ├── ValueObject/ # Immutable value objects
│       ├── Repository/  # Repository interfaces
│       ├── Service/     # Domain services
│       ├── Event/       # Domain events
│       └── Exception/   # Domain exceptions
├── Application/         # Use case orchestration
│   └── {BoundedContext}/
│       ├── Command/     # Command handlers (CQRS)
│       ├── Query/       # Query handlers (CQRS)
│       ├── DTO/         # Data Transfer Objects
│       └── Service/     # Application services
├── Infrastructure/      # Technical implementations
│   ├── Persistence/     # Repository implementations
│   ├── Event/           # Event listeners/dispatchers
│   └── External/        # External service integrations
└── Presentation/        # HTTP layer
    ├── Controller/      # HTTP controllers (thin layer)
    ├── Request/         # Request DTOs/Form types
    └── Response/        # Response DTOs
```

#### SOLID Principles
- **Single Responsibility**: Each class has one reason to change
- **Open/Closed**: Open for extension, closed for modification
- **Liskov Substitution**: Derived classes must be substitutable
- **Interface Segregation**: Create focused, specific interfaces
- **Dependency Inversion**: Depend on abstractions, not concretions

#### CQRS Pattern
- Commands for write operations (mutations)
- Queries for read operations
- Separate handlers for commands and queries

#### Repository Pattern
- Interfaces defined in Domain layer
- Implementations in Infrastructure layer
- Dependency injection through interfaces

### Testing Strategy

#### Test Organization
```
tests/
├── Unit/           # Domain layer tests (entities, value objects, services)
├── Feature/         # Application layer tests (use cases, endpoints)
└── Integration/     # Infrastructure layer tests
```

#### Test Types
- **Unit Tests**: Test individual classes in isolation, mock all dependencies
- **Feature Tests**: Test complete use cases end-to-end
- **Integration Tests**: Test infrastructure components with real implementations

#### Testing Best Practices
- Follow AAA pattern (Arrange, Act, Assert)
- Descriptive test names: `testShould{ExpectedBehavior}When{Condition}()`
- Use data providers for multiple scenarios
- Coverage goals:
  - Domain Layer: 100%
  - Application Layer: 90%+
  - Infrastructure Layer: 80%+
  - Presentation Layer: 70%+

#### Test Execution
```bash
php bin/phpunit                    # Run all tests
php bin/phpunit tests/Unit          # Run unit tests
php bin/phpunit tests/Feature       # Run feature tests
php bin/phpunit --coverage-html     # Generate coverage report
```

### Git Workflow

#### Commit Conventions
Follow Conventional Commits format:
- `feat:` - New features
- `fix:` - Bug fixes
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting, etc.)
- `chore:` - Maintenance tasks

#### Commit Message Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

Example:
```
feat(user): add user creation endpoint

- Add CreateUserController
- Add CreateUserCommand and Handler
- Add User entity and value objects
- Add unit tests for domain logic
```

## Domain Context

### Bounded Contexts
The project is organized around bounded contexts, each representing a distinct business domain. Each bounded context contains:
- Its own domain model (entities, value objects)
- Application services for use cases
- Repository interfaces
- Domain events

### Domain Modeling Principles
- **Entities**: Objects with identity, mutable state
- **Value Objects**: Immutable objects without identity
- **Aggregates**: Consistency boundaries with aggregate roots
- **Domain Services**: Stateless business logic
- **Application Services**: Orchestration layer for use cases

### Ubiquitous Language
Use domain terminology consistently throughout the codebase. Class names, method names, and documentation should reflect the business domain language.

## Important Constraints

### Technical Constraints
- **PHP Version**: Minimum PHP 8.4 required
- **Symfony Version**: Symfony 7.3.* (locked version)
- **Type Safety**: Strict types mandatory (`declare(strict_types=1);`)
- **No Symfony Full Stack**: Using individual Symfony components (not `symfony/symfony`)
- **Stability**: Minimum stability is "stable", prefer stable packages

### Architecture Constraints
- **Domain Layer Purity**: Domain layer must not depend on infrastructure
- **Dependency Direction**: Dependencies flow inward (Infrastructure → Application → Domain)
- **Interface Segregation**: Create focused interfaces, avoid large generic interfaces
- **Immutability**: Value objects must be immutable

### Code Quality Constraints
- All public methods must have type hints
- All classes should be `final` unless inheritance is required
- Use constructor property promotion (PHP 8.0+)
- Prefer composition over inheritance
- No deep nesting (max 3-4 levels)

## External Dependencies

### PHP Extensions
- `ext-ctype`: Character type checking
- `ext-iconv`: Character encoding conversion

### Symfony Components
- Core framework components (console, dotenv, framework-bundle, runtime, yaml)
- Additional components added via Symfony Flex as needed

### Development Dependencies
- PHPUnit (to be added for testing)
- Additional dev tools as needed

### Service Configuration
- Services registered in `config/services.yaml`
- Autowiring and autoconfiguration enabled by default
- Interface-to-implementation mapping via service aliases

### Environment Configuration
- Environment variables managed via `.env` files
- Configuration files in `config/` directory (YAML format)
- Runtime configuration via Symfony Dotenv component
