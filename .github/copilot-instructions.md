# Forumify Platform - Copilot Instructions

This document provides key information to help Copilot work effectively in the Forumify Platform repository.

## Quick Reference

- **Language**: PHP 8.4+
- **Framework**: Symfony 7.4
- **Database**: Doctrine ORM
- **Testing**: PHPUnit 11.5
- **Code Quality**: PSR-12 (via PHPCS), PHPStan level 8

## Build, Test & Lint Commands

### Quality Checks
```bash
make quality           # Run PHPCS (code style) + PHPStan (static analysis)
make quality-fix       # Auto-fix code style issues with phpcbf
```

### Individual Tools
```bash
./vendor/bin/phpcs               # Check code style (PSR-12)
./vendor/bin/phpcbf              # Auto-fix code style issues
./vendor/bin/phpstan analyze     # Run static analysis
./vendor/bin/phpunit             # Run all tests
```

### Test Execution
```bash
make tests             # Full test setup + run (Linux/WSL/macOS)
make setup-tests       # Setup test database and schema
make run-tests         # Run PHPUnit tests only
```

**Running a Single Test**:
```bash
./vendor/bin/phpunit tests/Tests/Application/Forum/ForumControllerTest.php
```

### Before Committing
Always run `make quality` to catch issues early. The CI pipeline enforces:
- PHPUnit tests pass
- PHPCS code style compliance (PSR-12)
- PHPStan analysis at level 8

## High-Level Architecture

Forumify is a modular forum platform built as a Symfony bundle. The codebase is organized by **functional domains** rather than technical layers.

### Core Modules (src/)

- **Admin**: Admin panel UI, CRUD controllers, admin-specific forms and menu builders
- **Forum**: Core forum functionality - forums, topics, posts, forum types, forum groups
- **Core**: User management, authentication, roles, permissions (ACL), settings, base entities
- **Cms**: Page management and content system
- **Api**: API Platform configuration, Doctrine customizations, serializers, event subscribers
- **Automation**: Scheduled tasks, automation rules, triggers, conditions, actions
- **OAuth**: OAuth provider integration
- **Plugin**: Plugin system and discovery

Each module typically contains:
- `Entity/`: Doctrine ORM entities
- `Repository/`: Doctrine repositories (extend `AbstractRepository`)
- `Controller/`: Request handlers
- `Form/`: Symfony form types
- `Service/`: Business logic
- `EventSubscriber/`: Event listeners
- `*Type.php`: Type classes (e.g., `ForumTypeInterface` implementations)

### Architecture Patterns

**Entities & Traits**:
- Entities use composition with traits for common functionality: `IdentifiableEntityTrait`, `SortableEntityTrait`, `TimestampableEntityTrait`
- Entities implement domain interfaces: `AccessControlledEntityInterface`, `AuditableEntityInterface`, `HierarchicalInterface`, `SortableEntityInterface`
- All entities use Doctrine attributes (`#[ORM\...]`) for mapping

**Repositories**:
- All repositories extend `AbstractRepository<EntityClass>`
- Must implement `getEntityClass()` static method
- Use constructor injection for dependencies
- Repositories are autowired by entity class: `UserRepository $userRepository`

**Serialization**:
- API Platform handles REST endpoints automatically with `#[ApiResource]` attributes
- Uses `#[Groups(...)]` for controlling serialization
- Custom serializers in `Api/Serializer/`

**Access Control**:
- ACL (Access Control List) system via `AccessControlledEntityInterface`
- Voter-based permission checks: `$this->denyAccessUnlessGranted(VoterAttribute::ACL->value, ['permission' => 'view', 'entity' => $entity])`
- Permissions stored in `core_acl_entry` database table

**Service Locator Pattern**:
- Critical services use `#[AutowireIterator(...)]` to collect tagged implementations (e.g., forum types, automation triggers)
- Tagged services are discovered via service configuration

### Testing Architecture

**Test Structure**:
- Application tests in `tests/Tests/Application/` - full HTTP stack with database (use `WebTestCase`)
- Unit tests in `tests/Tests/Unit/` - isolated component tests
- Test database is ephemeral (recreated each test run in `make setup-tests`)
- Zenstruck foundry factories in `tests/Tests/Factories` to create entities

**Test Traits** (`tests/Tests/Traits/`):
- `UserTrait`: Creates test users with `createUser()`, `createAdmin()`
- `ForumTrait`: Creates test forum entities with `createForum()`
- `ACLTrait`: Sets up ACL permissions with `createACL()`
- `SettingTrait`: Manages settings with `setSetting()`
- `RequiresContainerTrait`: Provides `self::getContainer()` access

No new traits should be created it is being phased out/deprecated, use zenstruck foundry factories instead.

**Test Pattern**:
```php
class MyTest extends WebTestCase {
    use UserTrait;
    use ForumTrait;

    public function testSomething(): void {
        $client = static::createClient();
        $user = $this->createUser();
        $forum = $this->createForum('Test');
        $client->loginUser($user);
        $client->request('GET', '/forum');
        self::assertResponseIsSuccessful();
    }
}
```

### Testing with Database
- Tests run in a test environment with a real MySQL database
- `make setup-tests` is only required once per session
- Use test fixtures/factories for consistent test data
- All tests automatically use database transactions with rollback

## Key Conventions

### Code Style & Structure

- **Declare strict types** at the top of every PHP file: `declare(strict_types=1);`
- **PSR-12 compliance** enforced by PHPCS (SlevomatCodingStandard rules included)
- **Early exit pattern**: Use early returns to avoid deep nesting (configured in PHPCS rules)
- **Attribute ordering**: Doctrine attributes before Symfony attributes (see phpcs.xml)
- **Yoda comparisons forbidden**: Use `if ($value === true)` not `if (true === $value)`

### Naming Conventions

- **Classes**: PascalCase (e.g., `ForumController`, `CreateUserService`)
- **Methods/properties**: camelCase (e.g., `createForum()`, `$forumTitle`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `TYPE_TEXT`, `PERMISSION_VIEW`)
- **Repository pattern**: `{Entity}Repository` (e.g., `ForumRepository`)
- **Service pattern**: `{Action}Service` (e.g., `CreateUserService`, `DeleteTopicService`)
- **Form pattern**: `{Entity}Type` (e.g., `ForumType`, `UserType`)
- **Type classes**: `{Name}Interface` or `{Name}Type` (e.g., `ForumTypeInterface`, `TriggerType`)

### Entity & Repository Patterns

- **Always use immutable collections**: `Collection<int, Entity>` in entity properties
- **Repository queries**: Prefer QueryBuilder in repositories, not in controllers
- **Doctrine relationships**: Use `#[ORM\OneToMany]`, `#[ORM\ManyToOne]`, lazy loading by default
- **Timestamp fields**: Use `TimestampableEntityTrait` for automatic `createdAt`/`updatedAt`
- **Slugs**: Use Gedmo `#[Gedmo\Slug(fields: ['title'])]` for automatic slug generation
- **Soft deletes**: Not currently used; hard deletes are standard

### Forms & Input Validation

- Form types in `{Module}/Form/{Entity}Type.php`
- Use Symfony Validator attributes: `#[Assert\NotBlank]`, `#[Assert\Email]`, etc.
- DTO classes in `Form/DTO/` for form input
- Custom constraints should be in `Core/Validator/`

### Services & Business Logic

- Services receive dependencies via constructor injection
- Repository instances auto-wired by type hint (no need for generic repository)
- Event subscribers in `{Module}/EventSubscriber/{Name}Subscriber.php`
- Use `ManagerRegistry->getRepository()` or type-hint the repository class directly
- Transactional operations use Doctrine's transaction handling or Symfony Messenger for async

### Routing

- Routes defined via `#[Route(...)]` attributes on controller methods
- Route names follow pattern: `{module}.{action}` or `{module}_{action}`
- Example: `#[Route('/forum/{slug:forum?}', name: 'forum')]`

### API Platform Integration

- Endpoints auto-exposed with `#[ApiResource]` on entities
- Operations configured at class level: `operations: [new Get(), new Post(), ...]`
- Serialization controlled via `#[Groups(...)]`
- Custom operations via `#[Route]` attributes on controller methods

### Plugins & Extensibility

- Third-party functionality extends via the plugin system (`src/Plugin/`)
- Plugins use tagged services for discovery
- Custom forum types implement `ForumTypeInterface` and register as `forumify.forum.type` service
- Custom automation triggers/conditions/actions register as tagged services

### IDE & Type Hints

- Full return types required: `public function getName(): string`
- Parameter types required: `private function validate(User $user): void`
- Use union types for multiple types: `string|int`
- Use `mixed` only when truly necessary
- Nullable types: `?User`, only use `|null` when more than 1 additional type
- PHPStan runs at level 8 (strictest checks enabled)

### Migrations
- Doctrine migrations stored in `migrations/` directory
- Always create migrations for schema changes: `php bin/console doctrine:migrations:generate`
- Migrations should be idempotent
- Always tell the user to manually inspect migrations and remove unrelated migrations
- Each PR should only have 1 migration, squash them together before creating a PR

## Related Repositories

- **Forumify Docs**: https://github.com/forumify/forumify-docs
- **Production Template**: https://github.com/forumify/forumify-production-template
- **Docker Image**: https://github.com/forumify/forumify-docker
- **Flex Recipes**: https://github.com/forumify/flex-recipes
