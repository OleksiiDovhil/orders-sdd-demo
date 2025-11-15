# OpenSpec Instructions

Instructions for AI coding assistants using OpenSpec for spec-driven development.

## TL;DR Quick Checklist

- Search existing work: `openspec spec list --long`, `openspec list` (use `rg` only for full-text search)
- Decide scope: new capability vs modify existing capability
- Pick a unique `change-id`: kebab-case, verb-led (`add-`, `update-`, `remove-`, `refactor-`)
- Scaffold: `proposal.md`, `tasks.md`, `design.md` (only if needed), and delta specs per affected capability
- Write deltas: use `## ADDED|MODIFIED|REMOVED|RENAMED Requirements`; include at least one `#### Scenario:` per requirement
- Validate: `openspec validate [change-id] --strict` and fix issues
- Request approval: Do not start implementation until proposal is approved

## Three-Stage Workflow

### Stage 1: Creating Changes
Create proposal when you need to:
- Add features or functionality
- Make breaking changes (API, schema)
- Change architecture or patterns  
- Optimize performance (changes behavior)
- Update security patterns

Triggers (examples):
- "Help me create a change proposal"
- "Help me plan a change"
- "Help me create a proposal"
- "I want to create a spec proposal"
- "I want to create a spec"

Loose matching guidance:
- Contains one of: `proposal`, `change`, `spec`
- With one of: `create`, `plan`, `make`, `start`, `help`

Skip proposal for:
- Bug fixes (restore intended behavior)
- Typos, formatting, comments
- Dependency updates (non-breaking)
- Configuration changes
- Tests for existing behavior

**Workflow**
1. Review `openspec/project.md`, `openspec list`, and `openspec list --specs` to understand current context.
2. Choose a unique verb-led `change-id` and scaffold `proposal.md`, `tasks.md`, optional `design.md`, and spec deltas under `openspec/changes/<id>/`.
3. Draft spec deltas using `## ADDED|MODIFIED|REMOVED Requirements` with at least one `#### Scenario:` per requirement.
4. Run `openspec validate <id> --strict` and resolve any issues before sharing the proposal.

### Stage 2: Implementing Changes
Track these steps as TODOs and complete them one by one.
1. **Read proposal.md** - Understand what's being built
2. **Read design.md** (if exists) - Review technical decisions
3. **Read tasks.md** - Get implementation checklist
4. **Implement tasks sequentially** - Complete in order
5. **Review database queries** - Check if query count can be reduced without breaking business rules or performance
6. **Confirm completion** - Ensure every item in `tasks.md` is finished before updating statuses
7. **Update checklist** - After all work is done, set every task to `- [x]` so the list reflects reality
8. **Approval gate** - Do not start implementation until the proposal is reviewed and approved

### Stage 3: Archiving Changes
After deployment, create separate PR to:
- Move `changes/[name]/` → `changes/archive/YYYY-MM-DD-[name]/`
- Update `specs/` if capabilities changed
- Use `openspec archive <change-id> --skip-specs --yes` for tooling-only changes (always pass the change ID explicitly)
- Run `openspec validate --strict` to confirm the archived change passes checks

## Before Any Task

**Context Checklist:**
- [ ] Read relevant specs in `specs/[capability]/spec.md`
- [ ] Check pending changes in `changes/` for conflicts
- [ ] Read `openspec/project.md` for conventions
- [ ] Run `openspec list` to see active changes
- [ ] Run `openspec list --specs` to see existing capabilities

**Before Creating Specs:**
- Always check if capability already exists
- Prefer modifying existing specs over creating duplicates
- Use `openspec show [spec]` to review current state
- If request is ambiguous, ask 1–2 clarifying questions before scaffolding

**When Creating tasks.md:**
- **MANDATORY**: Always include a "Test Execution and Validation" section
- This section must include tasks to run all tests using `make test`
- Include tasks to fix any failing tests after code changes
- Include tasks to run coverage tests (`make test-coverage`) and compare with threshold (`make test-coverage-check`)
- If coverage decreased, include task to analyze uncovered code (`make test-coverage-analyze`) and create/update tests to restore coverage
- Test execution is a gate - all tests must pass before work is considered complete

**Running PHP Commands:**
- **ALWAYS** run PHP-related commands inside the PHP container, never directly in the console
- Use `docker-compose exec php <command>` or `docker exec symfony-php <command>`
- Examples:
  - `docker-compose exec php bin/console <command>` for Symfony console commands
  - `docker-compose exec php composer <command>` for Composer commands
  - `docker-compose exec php php <script>` for PHP scripts
- The container name is `symfony-php` and the service name is `php`

### Search Guidance
- Enumerate specs: `openspec spec list --long` (or `--json` for scripts)
- Enumerate changes: `openspec list` (or `openspec change list --json` - deprecated but available)
- Show details:
  - Spec: `openspec show <spec-id> --type spec` (use `--json` for filters)
  - Change: `openspec show <change-id> --json --deltas-only`
- Full-text search (use ripgrep): `rg -n "Requirement:|Scenario:" openspec/specs`

## Quick Start

### CLI Commands

```bash
# Essential commands
openspec list                  # List active changes
openspec list --specs          # List specifications
openspec show [item]           # Display change or spec
openspec validate [item]       # Validate changes or specs
openspec archive <change-id> [--yes|-y]   # Archive after deployment (add --yes for non-interactive runs)

# Project management
openspec init [path]           # Initialize OpenSpec
openspec update [path]         # Update instruction files

# Interactive mode
openspec show                  # Prompts for selection
openspec validate              # Bulk validation mode

# Debugging
openspec show [change] --json --deltas-only
openspec validate [change] --strict
```

### Command Flags

- `--json` - Machine-readable output
- `--type change|spec` - Disambiguate items
- `--strict` - Comprehensive validation
- `--no-interactive` - Disable prompts
- `--skip-specs` - Archive without spec updates
- `--yes`/`-y` - Skip confirmation prompts (non-interactive archive)

## Directory Structure

```
openspec/
├── project.md              # Project conventions
├── specs/                  # Current truth - what IS built
│   └── [capability]/       # Single focused capability
│       ├── spec.md         # Requirements and scenarios
│       └── design.md       # Technical patterns
├── changes/                # Proposals - what SHOULD change
│   ├── [change-name]/
│   │   ├── proposal.md     # Why, what, impact
│   │   ├── tasks.md        # Implementation checklist
│   │   ├── design.md       # Technical decisions (optional; see criteria)
│   │   └── specs/          # Delta changes
│   │       └── [capability]/
│   │           └── spec.md # ADDED/MODIFIED/REMOVED
│   └── archive/            # Completed changes
```

## Creating Change Proposals

### Decision Tree

```
New request?
├─ Bug fix restoring spec behavior? → Fix directly
├─ Typo/format/comment? → Fix directly  
├─ New feature/capability? → Create proposal
├─ Breaking change? → Create proposal
├─ Architecture change? → Create proposal
└─ Unclear? → Create proposal (safer)
```

### Proposal Structure

1. **Create directory:** `changes/[change-id]/` (kebab-case, verb-led, unique)

2. **Write proposal.md:**
```markdown
# Change: [Brief description of change]

## Why
[1-2 sentences on problem/opportunity]

## What Changes
- [Bullet list of changes]
- [Mark breaking changes with **BREAKING**]

## Impact
- Affected specs: [list capabilities]
- Affected code: [key files/systems]
```

3. **Create spec deltas:** `specs/[capability]/spec.md`
```markdown
## ADDED Requirements
### Requirement: New Feature
The system SHALL provide...

#### Scenario: Success case
- **WHEN** user performs action
- **THEN** expected result

## MODIFIED Requirements
### Requirement: Existing Feature
[Complete modified requirement]

## REMOVED Requirements
### Requirement: Old Feature
**Reason**: [Why removing]
**Migration**: [How to handle]
```
If multiple capabilities are affected, create multiple delta files under `changes/[change-id]/specs/<capability>/spec.md`—one per capability.

4. **Create tasks.md:**
```markdown
## 1. Implementation
- [ ] 1.1 Create database schema
- [ ] 1.2 Implement API endpoint
- [ ] 1.3 Add frontend component
- [ ] 1.4 Write tests
- [ ] 1.5 Review and optimize database queries (reduce query count if possible, without breaking business rules or performance)
- [ ] 1.6 Replace string literals with Enums/constants (messages, status values) - convert to strings only in presentation layer

## X. Test Execution and Validation
- [ ] X.1 Run PHPStan for src folder: `make phpstan-src` and fix any issues found
- [ ] X.2 Run CodeSniffer for src folder: `make phpcbf-src` to auto-fix issues, then `make phpcs-src` to verify
- [ ] X.3 Fix any remaining CodeSniffer violations in src folder that phpcbf could not auto-fix
- [ ] X.4 Run deptrac: `make deptrack` and fix any architectural violations found
- [ ] X.5 Run all tests: `make test` and verify all tests pass with exit code 0
- [ ] X.6 Fix any failing tests that may have been broken by code changes
- [ ] X.7 Run PHPStan globally (with tests folder): `make phpstan` and fix any issues found
- [ ] X.8 Run CodeSniffer globally (with tests folder): `make phpcbf` to auto-fix issues, then `make phpcs` to verify
- [ ] X.9 Fix any remaining CodeSniffer violations that phpcbf could not auto-fix
- [ ] X.10 Run automated coverage fix workflow: `make test-coverage-auto-fix` (or manually follow steps X.11-X.14)
- [ ] X.11 Run tests with coverage: `make test-coverage` to generate coverage report
- [ ] X.12 Run coverage check: `make test-coverage-check` to compare current coverage with `coverage_percent` file
- [ ] X.13 If coverage check failed (coverage decreased): 
  - Run `make test-coverage-auto-fix` to get prioritized list of uncovered code
  - The tool will prioritize recently created/edited files (from git diff) as HIGHEST PRIORITY
  - Start adding tests for classes in priority order (highest priority first)
  - After each test addition, run `make test-coverage-check` to verify improvement
  - Continue until coverage check passes
- [ ] X.14 Verify test coverage is maintained or improved after code changes
```

**CRITICAL**: Every `tasks.md` MUST include a "Test Execution and Validation" section with explicit tasks to run tests after code changes. This is mandatory regardless of whether new tests are written or only existing code is modified.

5. **Create design.md when needed:**
Create `design.md` if any of the following apply; otherwise omit it:
- Cross-cutting change (multiple services/modules) or a new architectural pattern
- New external dependency or significant data model changes
- Security, performance, or migration complexity
- Ambiguity that benefits from technical decisions before coding

Minimal `design.md` skeleton:
```markdown
## Context
[Background, constraints, stakeholders]

## Goals / Non-Goals
- Goals: [...]
- Non-Goals: [...]

## Decisions
- Decision: [What and why]
- Alternatives considered: [Options + rationale]

## Risks / Trade-offs
- [Risk] → Mitigation

## Migration Plan
[Steps, rollback]

## Open Questions
- [...]
```

## Spec File Format

### Critical: Scenario Formatting

**CORRECT** (use #### headers):
```markdown
#### Scenario: User login success
- **WHEN** valid credentials provided
- **THEN** return JWT token
```

**WRONG** (don't use bullets or bold):
```markdown
- **Scenario: User login**  ❌
**Scenario**: User login     ❌
### Scenario: User login      ❌
```

Every requirement MUST have at least one scenario.

### Requirement Wording
- Use SHALL/MUST for normative requirements (avoid should/may unless intentionally non-normative)

### Delta Operations

- `## ADDED Requirements` - New capabilities
- `## MODIFIED Requirements` - Changed behavior
- `## REMOVED Requirements` - Deprecated features
- `## RENAMED Requirements` - Name changes

Headers matched with `trim(header)` - whitespace ignored.

#### When to use ADDED vs MODIFIED
- ADDED: Introduces a new capability or sub-capability that can stand alone as a requirement. Prefer ADDED when the change is orthogonal (e.g., adding "Slash Command Configuration") rather than altering the semantics of an existing requirement.
- MODIFIED: Changes the behavior, scope, or acceptance criteria of an existing requirement. Always paste the full, updated requirement content (header + all scenarios). The archiver will replace the entire requirement with what you provide here; partial deltas will drop previous details.
- RENAMED: Use when only the name changes. If you also change behavior, use RENAMED (name) plus MODIFIED (content) referencing the new name.

Common pitfall: Using MODIFIED to add a new concern without including the previous text. This causes loss of detail at archive time. If you aren’t explicitly changing the existing requirement, add a new requirement under ADDED instead.

Authoring a MODIFIED requirement correctly:
1) Locate the existing requirement in `openspec/specs/<capability>/spec.md`.
2) Copy the entire requirement block (from `### Requirement: ...` through its scenarios).
3) Paste it under `## MODIFIED Requirements` and edit to reflect the new behavior.
4) Ensure the header text matches exactly (whitespace-insensitive) and keep at least one `#### Scenario:`.

Example for RENAMED:
```markdown
## RENAMED Requirements
- FROM: `### Requirement: Login`
- TO: `### Requirement: User Authentication`
```

## Troubleshooting

### Common Errors

**"Change must have at least one delta"**
- Check `changes/[name]/specs/` exists with .md files
- Verify files have operation prefixes (## ADDED Requirements)

**"Requirement must have at least one scenario"**
- Check scenarios use `#### Scenario:` format (4 hashtags)
- Don't use bullet points or bold for scenario headers

**Silent scenario parsing failures**
- Exact format required: `#### Scenario: Name`
- Debug with: `openspec show [change] --json --deltas-only`

### Validation Tips

```bash
# Always use strict mode for comprehensive checks
openspec validate [change] --strict

# Debug delta parsing
openspec show [change] --json | jq '.deltas'

# Check specific requirement
openspec show [spec] --json -r 1
```

## Happy Path Script

```bash
# 1) Explore current state
openspec spec list --long
openspec list
# Optional full-text search:
# rg -n "Requirement:|Scenario:" openspec/specs
# rg -n "^#|Requirement:" openspec/changes

# 2) Choose change id and scaffold
CHANGE=add-two-factor-auth
mkdir -p openspec/changes/$CHANGE/{specs/auth}
printf "## Why\n...\n\n## What Changes\n- ...\n\n## Impact\n- ...\n" > openspec/changes/$CHANGE/proposal.md
printf "## 1. Implementation\n- [ ] 1.1 ...\n" > openspec/changes/$CHANGE/tasks.md

# 3) Add deltas (example)
cat > openspec/changes/$CHANGE/specs/auth/spec.md << 'EOF'
## ADDED Requirements
### Requirement: Two-Factor Authentication
Users MUST provide a second factor during login.

#### Scenario: OTP required
- **WHEN** valid credentials are provided
- **THEN** an OTP challenge is required
EOF

# 4) Validate
openspec validate $CHANGE --strict
```

## Multi-Capability Example

```
openspec/changes/add-2fa-notify/
├── proposal.md
├── tasks.md
└── specs/
    ├── auth/
    │   └── spec.md   # ADDED: Two-Factor Authentication
    └── notifications/
        └── spec.md   # ADDED: OTP email notification
```

auth/spec.md
```markdown
## ADDED Requirements
### Requirement: Two-Factor Authentication
...
```

notifications/spec.md
```markdown
## ADDED Requirements
### Requirement: OTP Email Notification
...
```

## Best Practices

### Simplicity First
- Default to <100 lines of new code
- Single-file implementations until proven insufficient
- Avoid frameworks without clear justification
- Choose boring, proven patterns
- **Never create unused methods, functions, or classes**
- Only implement what is required by the current use case
- Apply YAGNI (You Aren't Gonna Need It) principle strictly

### Database Query Optimization Checklist
- [ ] Review all database queries in the implementation
- [ ] Check if multiple queries can be combined into one (e.g., using JOINs)
- [ ] Verify entity properties are loaded in initial query rather than separate lookups
- [ ] Ensure no N+1 query problems exist
- [ ] Confirm optimization doesn't break business rules
- [ ] Verify optimization doesn't negatively impact performance
- [ ] Test that all functionality still works correctly after optimization

### String Literals and Constants Checklist
- [ ] Review code for string literals in business logic (handlers, services, domain logic)
- [ ] Replace string literals with Enums for messages, status values, or constants
- [ ] Verify DTOs use enum types, not string types for these values
- [ ] Ensure string conversion (`getValue()`) happens only in controllers/presentation layer
- [ ] Confirm all string literals are either in Enums or configuration files
- [ ] Test that enum values are correctly converted to strings in JSON responses

### Controller and Request Best Practices
- **Always create Request DTOs** - Never parse JSON manually in controllers
- **Request DTOs must have `createCommand()` method** - Convert request to application command
- **Use ValueResolver for automatic injection** - Request DTOs are injected as controller parameters
- **Use Symfony Validation attributes** - Declarative validation on request DTOs
- **Controllers must be thin** - Only orchestrate HTTP concerns, delegate to handlers
- **Use exception listeners** - Handle validation errors globally, not in controllers
- **Never manually validate** - Validation happens automatically before controller is called

### Repository and Interface Design
- **Only add repository methods that are actually used in the implementation**
- Don't add "convenience" methods like `findById`, `findByX`, `getNextId` unless explicitly required
- Before adding a method, verify it will be called from application handlers or services
- After implementation, review and remove any unused methods
- If a method seems useful but isn't used, document why it's needed or remove it
- Prefer implementing the feature first, then adding methods only as needed

### Database Query Optimization
- **Always review database queries for optimization opportunities** - Check if query count can be reduced
- **Load entity properties in initial query** - Include all needed fields (e.g., `is_paid`) when fetching entities rather than separate queries
- **Combine queries when possible** - Use JOINs or single queries instead of multiple separate queries
- **Avoid N+1 query problems** - Use eager loading or batch queries when fetching related data
- **Optimize only when safe** - Never optimize if it would:
  - Break business rules or domain invariants
  - Negatively impact performance (e.g., loading unnecessary large datasets)
  - Compromise data consistency or transaction integrity
  - Make code significantly more complex without clear benefit
- **Example**: When checking order payment status, load `is_paid` in the `findByUniqueOrderNumber()` query rather than making a separate `isPaid()` query

### String Literals and Constants
- **MUST NOT use string literals in business logic** - Use Enums, constants, or value objects instead
- **Use Enums for messages and status values** - Create typed enums with descriptive case names (e.g., `OrderCompletionMessage::PAID`, `OrderCompletionMessage::PENDING`)
- **Keep string values in Enums** - Store actual string values as enum case values, not in business logic
- **Convert to string only in presentation layer** - Call `getValue()` or similar methods only in controllers when converting to JSON/HTTP responses
- **DTOs should use Enums, not strings** - Response DTOs should accept enum types, controllers convert to strings
- **Benefits**: Type safety, centralized message management, easier refactoring, prevents typos
- **Example**: Instead of `'Order has been paid successfully'` in handler, use `OrderCompletionMessage::PAID` enum case

### Testing Requirements
- **MANDATORY: Unit tests for Request DTOs** - Always test `createCommand()` methods
- **MANDATORY: Feature tests for all validation scenarios** - Test every validation constraint
- **MANDATORY: Feature tests for error responses** - Verify error structure (`errors` or `error` key)
- **MANDATORY: Feature tests for success cases** - Test happy path with valid data
- **MANDATORY: Run all tests after code changes** - Use `make test` command
- **MANDATORY: Fix failing tests before completion** - Never leave tests in a failing state
- **MANDATORY: Include test execution tasks in tasks.md** - Every proposal MUST have a "Test Execution and Validation" section with explicit tasks following the standard workflow (phpstan-src, codesniffer-src, deptrack, test, phpstan, codesniffer, test-coverage, test-coverage-check, test-coverage-analyze if coverage decreased)
- Test missing required fields, invalid types, invalid values, invalid JSON format
- Verify specific error messages in validation tests
- Request DTOs must have 100% test coverage
- **Test execution is a gate** - All tests must pass before work is considered complete
- **Test execution tasks are required** - Even if no new tests are written, existing tests must be run and verified after any code changes

### Complexity Triggers
Only add complexity with:
- Performance data showing current solution too slow
- Concrete scale requirements (>1000 users, >100MB data)
- Multiple proven use cases requiring abstraction

### Clear References
- Use `file.ts:42` format for code locations
- Reference specs as `specs/auth/spec.md`
- Link related changes and PRs

### Capability Naming
- Use verb-noun: `user-auth`, `payment-capture`
- Single purpose per capability
- 10-minute understandability rule
- Split if description needs "AND"

### Change ID Naming
- Use kebab-case, short and descriptive: `add-two-factor-auth`
- Prefer verb-led prefixes: `add-`, `update-`, `remove-`, `refactor-`
- Ensure uniqueness; if taken, append `-2`, `-3`, etc.

## Tool Selection Guide

| Task | Tool | Why |
|------|------|-----|
| Find files by pattern | Glob | Fast pattern matching |
| Search code content | Grep | Optimized regex search |
| Read specific files | Read | Direct file access |
| Explore unknown scope | Task | Multi-step investigation |

## Error Recovery

### Change Conflicts
1. Run `openspec list` to see active changes
2. Check for overlapping specs
3. Coordinate with change owners
4. Consider combining proposals

### Validation Failures
1. Run with `--strict` flag
2. Check JSON output for details
3. Verify spec file format
4. Ensure scenarios properly formatted

### Missing Context
1. Read project.md first
2. Check related specs
3. Review recent archives
4. Ask for clarification

## Quick Reference

### Stage Indicators
- `changes/` - Proposed, not yet built
- `specs/` - Built and deployed
- `archive/` - Completed changes

### File Purposes
- `proposal.md` - Why and what
- `tasks.md` - Implementation steps
- `design.md` - Technical decisions
- `spec.md` - Requirements and behavior

### CLI Essentials
```bash
openspec list              # What's in progress?
openspec show [item]       # View details
openspec validate --strict # Is it correct?
openspec archive <change-id> [--yes|-y]  # Mark complete (add --yes for automation)
```

Remember: Specs are truth. Changes are proposals. Keep them in sync.
