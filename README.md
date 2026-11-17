# GreenJeans

A PHP client for interacting with the Geni.com API to manage, query, and synchronize genealogical data with a local relational database.

## Requirements & Installation

- PHP 8.x+
- Optional Database: MySQL or SQLite (supported by the schemas in `resources/sql`)

Install via Composer:

```bash
composer require shmuelakam/greenjeans
```

### Local Configuration

Before running the application, create your local configuration files from the provided examples in the project root:

```bash
cp db.local.php.example   db.local.php
cp geni.local.php.example geni.local.php
```

- **`db.local.php`** — Holds your local or production database connection settings (host, database name, username, and password). Edit this file with your MySQL credentials (SQLite requires only a username).
- **`geni.local.php`** — Holds your Geni API application credentials (client ID, client secret, and redirect URI, if relevant). Edit this file with your registered Geni app details.

> **Note:** Both `*.local.php` files are excluded from Git via `.gitignore` and will not be overwritten during deployments.

### Database Setup

If you want to use MySQL for the persistence layer, initialize your database with the `resources/sql/full-schema.sql` schema file. No setup is required for SQLite, as the schema is initialized automatically at runtime.

```bash
mysql -u your_user -p your_database < resources/sql/full-schema.sql
```

### Getting Started

The `src/Api/Auth` directory contains PHP scripts for the three types of server authentication that Geni supports (see [Geni OAuth Documentation](https://www.geni.com/platform/developer/help/oauth)). Copy the relevant file to your application directory, adjust the `PACKAGE_ROOT` constant, and hook in your code.

---

## Architecture & Namespace Overview

The codebase is organized under `src/` into two primary domains: **API communication (`Api`)** and **Database storage (`Persistence`)**.

```text
ShmuelAKam\GreenJeans\
├── Api\                 # High-level domain models (API_Profile, API_Project, etc.)
│   ├── Auth\            # Scripts for server authentication & session/token handling
│   ├── Core\            # HTTP server wrapping engine & paginated list handlers
│   └── Data\            # Plain Old Data Objects (PODOs) representing sub-objects
│                        # without standalone APIs (API_Event, API_Location, etc.)
│
└── Persistence\         # Local database mapping & storage
    ├── Manage\          # Mapper factories & high-level Persistor orchestrators
    └── Mappers\         # High-level domain mappers
        ├── Core\        # Base mapper abstractions & join/link-table handlers
        └── Data\        # Low-level PODO mappers
```

### 1. API Layer (`ShmuelAKam\Greenjeans\Api`)

An object-oriented SDK acting as a server-connection wrapper, with classes for Geni API objects including media files (Document, Photo, Video), Profiles, Projects, "etc, etc, etc". Responsible for communicating with the remote service and hydrating responses into typed PHP objects. See [Api/Api.md](Api/Api.md) for a detailed guide to the significant classes in each sub-namespace.

The SDK can be used independently of the database persistence layer.

- **`ShmuelAKam\Greenjeans\Api`**
  Contains the primary, higher-level domain object classes that expose business operations and orchestrate API interactions.
- **`ShmuelAKam\Greenjeans\Api\Auth`**
  Scripts to handle the three types of server authentication that Geni provides.
- **`ShmuelAKam\Greenjeans\Api\Core`**
  The underlying transport and pagination engine. Wraps server requests/responses and manages paginated result sets (`paging-list` classes) so higher-level classes don't deal with raw HTTP mechanics. Users do not (and should not) interact directly with these wrapper classes.
- **`ShmuelAKam\Greenjeans\Api\Data`**
  Lightweight Plain Old Data Objects (PODOs) representing passive sub-objects that do not have their own standalone API endpoints (`API_Event`, `API_Location`, etc.).

### 2. Persistence Layer (`ShmuelAKam\Greenjeans\Persistence`)

Responsible for mapping API domain objects and PODOs to relational database tables, isolating SQL and storage logic from the API layer. Provides full CRUD operations for supported objects across both SQLite and MySQL.

- **`ShmuelAKam\Greenjeans\Persistence\Manage`**
  Entry point for storage operations. Contains the **Mapper Factory** (for resolving the appropriate mapper) and the **Persistor** class (the root orchestrator for managing persistence workflows).
- **`ShmuelAKam\Greenjeans\Persistence\Mappers`**
  High-level mappers responsible for persisting composite domain objects from `ShmuelAKam\Greenjeans\Api`.
- **`ShmuelAKam\Greenjeans\Persistence\Mappers\Core`**
  Foundational database abstractions, including the base mapper class and specialized handlers for one-to-many link/junction tables.
- **`ShmuelAKam\Greenjeans\Persistence\Mappers\Data`**
  Granular mappers dedicated to reading and writing `ShmuelAKam\Greenjeans\Api\Data` PODOs directly to their corresponding database tables.

---

## Project Directory Map

Beyond `src/`, the repository includes tooling for database management, testing, and deployment:

- **`bin/`** — CLI executables and maintenance scripts.
- **`resources/sql/`** — Database schema creation (`full-schema.sql`) and teardown SQL scripts.
- **`tests/Api/`** — API test suite featuring a custom HTTP mocking/recording engine (`Core`), JSON fixtures, media test files, and cleanup ("shredder") logs for live test runs.
- **`tests/Persistence/`** — Database integration tests verifying both PODO mappers and high-level domain mappers.
- **`phpstan.neon`** — Static analysis configuration.
- **`phpunit.api.xml` / `phpunit.persistence.xml`** — Separate PHPUnit configurations allowing independent execution of API and database test suites.

---

## Testing & Quality Assurance

The test suite is split so you can run API tests (with mocking/recording) independently from database persistence tests. The testing directory tree and setup are described in [`tests/Testing.md`](tests/Testing.md).

```bash
# Run API tests
vendor/bin/phpunit -c phpunit.api.xml

# Run Persistence tests
vendor/bin/phpunit -c phpunit.persistence.xml

# Run static analysis
vendor/bin/phpstan analyse
```

### Code Style & Static Analysis

- **PHPStan:** Code is validated with PHPStan and currently passes at **Level 6** (higher levels add noise without meaningful value for this codebase).
- **PHP-CS-Fixer:** Enforces **PER-CS**, with a few intentional preferences layered on top—specifically Allman-style braces and aligned `=` assignment operators.

---

## Disclaimer

This project is an unofficial, community-maintained SDK and persistence wrapper for the Geni.com API. It is not affiliated with, endorsed by, sponsored by, or officially connected to Geni.com or MyHeritage.

All product names, logos, and brands (including Geni and MyHeritage) are the property of their respective owners. Users of this library are responsible for complying with the [Geni.com Terms of Use](https://www.geni.com), API rate limits, and applicable data privacy laws when storing or processing genealogical data.