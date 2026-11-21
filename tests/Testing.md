# Testing Guide

The `greenjeans` test suite is divided into two independent suites—**API** and **Persistence**—so you can test remote API interactions (via recorded fixtures or live calls) separately from local database mapping. There are no end-to-end tests. For general installation and architecture details, see [`README.md`](../README.md), [`src/Api/Api.md`](../src/Api/Api.md), and [`src/Persistence/Persistence.md`](../src/Persistence/Persistence.md).

---

## Directory Layout

```text
greenjeans/
├── bin/
│   ├── api-test-full.sh          # Run the full API test suite
│   ├── db-test-full.sh           # Run the full Persistence test suite
│   ├── test-api-specific.sh      # Run targeted API test cases/files
│   ├── test-db-specific.sh       # Run targeted Persistence test cases/files
│   ├── phpstan.sh                # Run PHPStan static analysis
│   ├── deploy.sh                 # Deploy package source code
│   └── deploy-tests.sh           # Deploy test suite independently
├── tests/
│   ├── Api/                      # Test classes for high-level API entities
│   │   ├── GlobalSetup.php       # Controls API test run-mode ($runType)
│   │   ├── Core/                 # HTTP mocking & fixture recording engine
│   │   ├── files/                # Sample media files (documents, photos, videos) for upload tests
│   │   ├── fixtures/             # Recorded JSON response fixtures (*.json)
│   │   ├── Help/                 # Test object factories and live-run cleanup ("shredder") utilities
│   │   ├── log/                  # Temporary tracking logs for "shredding" (deleting) live test objects
│   │   └── Mockery/              # Mock classes simulating API responses and transport behavior
│   └── Persistence/              # Test classes for high-level domain mappers
│       ├── GlobalSetup.php       # Controls target database engine ($DBType)
│       ├── Data/                 # Test classes for low-level PODO mappers
│       └── Help/                 # DB_Persistor & mapper helpers for setting up test persistence contexts
├── config.test.server.php        # Configuration file specific to testing: URL of test server.
├── phpunit.api.xml               # PHPUnit configuration for the API suite
└── phpunit.persistence.xml       # PHPUnit configuration for the Persistence suite
```

---

## Running the Test Suites

### Via Helper Scripts (`bin/`)

The `bin/` directory provides wrapper scripts for running full suites, targeted test files, static analysis, and deployments:

- **`bin/api-test-full.sh`** — Runs the entire API test suite (`phpunit.api.xml`).

- **`bin/test-api-specific.sh`** — Runs a specific API test class or method.

- **`bin/db-test-full.sh`** — Runs the entire Persistence test suite (`phpunit.persistence.xml`).

- **`bin/test-db-specific.sh`** — Runs a specific Persistence test class or method.

- **`bin/phpstan.sh`** — Runs PHPStan static analysis against the codebase.

- **`bin/deploy.sh`** — Deploys the package source files.

- **`bin/deploy-tests.sh`** — Deploys the test suite separately from production code. Includes the fixtures and media files (121 MB total).

### Via PHPUnit Directly

You can also invoke PHPUnit directly using each suite's configuration file:

```bash
# Run the API test suite
vendor/bin/phpunit -c phpunit.api.xml

# Run the Persistence test suite
vendor/bin/phpunit -c phpunit.persistence.xml
```

---

## 1. API Test Suite (`tests/Api`)

Because the SDK (`ShmuelAKam\Greenjeans\Api`) communicates with the live Geni.com API, `tests/Api` includes infrastructure to run tests safely offline without hitting rate limits or leaving orphaned test data on Geni.

### Mocking & Recording Engine (`Core`, `Mockery`, `fixtures`)

The execution mode is controlled by the `$runType` variable in `tests/Api/GlobalSetup.php`, which supports three options: **normal** (online), **mocking** (offline), and **recording** (online).

- **Normal Online:** Tests the SDK by making live API calls against Geni.com. Executes **6,800+ API calls** and takes roughly **two hours** to complete.

- **Offline / Mocked Runs:** Replays recorded HTTP responses from `tests/Api/fixtures/*.json` through the `Core` engine and `Mockery` classes. Enables fast, semi-deterministic (some test data is randomized) test runs without network access or live API credentials.

- **Recording New Fixtures:** When adding tests for new endpoints or updating API behavior, the `Core` recording engine captures live API responses and serializes them into `tests/Api/fixtures/` for future offline runs. Generates roughly **5,000 fixtures** (not all are actively used, and several fixtures were crafted manually).

### Media Fixtures (`files/`)

Contains sample documents, photos, and videos used to verify multipart upload and media attachment workflows (`API_Document`, `API_Photo`, `API_Video`).

### Live Test Cleanup & "Shredding" (`Help/` & `log/`)

When running integration tests against the live Geni API (`normal` or `recording` mode), tests create temporary profiles and media objects:
- As objects are created during a live run, their IDs are recorded in temporary log files inside `tests/Api/log/`.
- Cleanup utilities in `tests/Api/Help/` read these logs to "shred" (delete) all test-created objects from Geni afterward, leaving the remote account clean. This can take an additional hour.

### Local Configuration

Before running the application, create your local configuration files from the provided examples in the project root:

```bash
cp config.test.server.php.example   config.test.server.php
```

- **`config.test.server.php`** — Holds the URL of your test server.

> **Note:** The `config.test.server.php` files is excluded from Git via `.gitignore` and will not be overwritten during deployments.

---

## 2. Persistence Test Suite (`tests/Persistence`)

The persistence suite verifies database CRUD operations and object graph mapping across supported backends (SQLite and MySQL) for `ShmuelAKam\Greenjeans\Persistence`. The target database engine is controlled by the `$DBType` variable in `tests/Persistence/GlobalSetup.php`, which accepts two options: **SQLite** and **MySQL**. The Persistence Test Suite does NOT make any API calls (no end-to-end tests).

- **`tests/Persistence/` (Root)** — Integration tests for high-level domain mappers (verifying that composite entities like `API_Profile`, `API_Union`, and `API_Relation` persist along with their sub-objects and link tables).

- **`tests/Persistence/Data/`** — Focused unit/integration tests for granular PODO mappers (`API_Event`, `API_Location`, `API_Date`, etc.).

- **`tests/Persistence/Help/`** — Helper classes that initialize `DB_Persistor` sessions (scoped by `run_id`), wire up mappers, and configure test fixtures cleanly across test cases.

### Database Prerequisites

- **SQLite:** Requires no manual setup; the test database schema is initialized automatically at runtime.

- **MySQL:** Ensure your test database credentials are configured in `db.local.php` and the schema from `resources/sql/full-schema.sql` has been loaded before running `phpunit.persistence.xml`.
