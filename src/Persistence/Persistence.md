# `ShmuelAKam\Greenjeans\Persistence` Overview

The `ShmuelAKam\Greenjeans\Persistence` namespace tree provides an optional relational database persistence layer using the **Data Mapper** pattern. It keeps all SQL queries, schema mapping, and run tracking isolated inside dedicated mapper classes, enabling local caching, offline tree traversal, and auditable sync runs.

> **Core Concepts:**
> - **Unit-of-Work (`run_id`):** Every database session is scoped to a unique `run_id` managed by `DB_RunMapper` (in `geni_runs`). All mappers receive this `run_id` upon creation and stamp persisted rows with it, grouping all inserts and updates from a single application execution into an auditable unit of work that can be marked `complete()`, `fail()`, or cleaned up together.
>
> - **API / Persistence Boundary:** While `Api` entities contain no SQL or database connection logic, select hydration and serialization methods (`newFromArray()`, `updateFromArray()`, and `makeParamArray()`) accept `$fromDB` / `$forDB` flags. When invoked by the persistence layer, these flags allow entities to hydrate or export sub-collections and lists (such as media lists, project rosters, or user metadata) in a single pass—data that the Geni REST API normally omits from standard object responses and requires separate lazy calls to fetch.

---

## Namespace Structure

```text
ShmuelAKam\Greenjeans\Persistence\
├── Manage\             # Session orchestration, mapper instantiation, sync runs, and cleanup
└── Mappers\            # Top-level domain entity mappers (one per primary API entity)
    ├── Core\           # Base mapper abstraction and many-to-many link-table handler
    └── Data\           # Child PODO and sub-component mappers
```

---

## 1. Persistence Management (`ShmuelAKam\Greenjeans\Persistence\Manage`)

`DB_Persistor` is the primary session and mapper entry point for application code interacting with the database. Callers initialize `DB_Persistor` with a `PDO` connection and username, obtain individual mappers via `\$persistor->getMapper($target)`, and finalize the sync run via `complete()` or `fail()`—never instantiating `DB_MapperFactory` directly. (For offline retention maintenance across past runs, `DB_CleanupManager` is instantiated directly with `PDO`.)

- **`DB_Persistor`**      — Session coordinator and mapper registry. Resolves the user's `client_id` from `geni_clients`, starts a tracked run via `DB_RunMapper`, wraps `DB_MapperFactory` internally (`getMapper(string|object $target)`), and exposes run lifecycle controls (`complete()`, `fail()`, `getRunID()`, `getPDO()`).

- **`DB_MapperFactory`**  — *(Internal)* Factory wrapped by `DB_Persistor` that dynamically resolves an `Api` class or object instance to its corresponding `Persistence\Mappers` class, caches one instance per mapper type for the session, and wires each mapper with `DB_Persistor`, `PDO`, and `runID` (plus `profileID` for `DB_UserMapper`).

- **`DB_RunMapper`**      — Manages synchronization/session run records in `geni_runs`. Automatically inserts a new run on creation (capturing `runID` and `start_time`), resolves the client's `profileID` by joining `geni_clients`, and logs execution duration (`duration_s`), peak memory (`memory_alloc_mb`), and completion/failure status via `complete()`, `fail()`, or an idempotent `__destruct()` fallback.

- **`DB_CleanupManager`** — Standalone maintenance utility (`new DB_CleanupManager(\$pdo)`) for purging synchronization history from `geni_runs`, relying on database `ON DELETE CASCADE` constraints to remove all associated records automatically (`deleteRun()`, `keepLastRuns()`, `deleteRunsOlderThan()`).

---

## 2. Primary Entity Mappers (`ShmuelAKam\Greenjeans\Persistence\Mappers`)

Mappers in the root `Mappers` namespace correspond directly to top-level `Api` domain entities (with the sole exception of `DB_RelationMapper`, which promotes `Api\Data\API_Relation` edges to a top-level mapper alongside profiles and unions). Rather than instantiating these directly, callers retrieve them via `\$persistor->getMapper(...)`:

### Core Genealogical & Account Mappers

- **`DB_ProfileMapper`**    — Maps `API_Profile` records in `geni_profiles` and localized strings in `geni_profile_details`. Uses a sub-object replacement pattern on update, delegates foreign-keyed sub-objects (`birth`, `death`, `burial`, `baptism`, `current_residence`, `location`, `mugshot_urls`) and child collections (`events`, `names`, `phone_numbers`) to `Mappers\Data`, and persists association lists (`managers`, `requested_merges`, `unions`, `projects`, plus `documents`/`photos`/`videos` when loaded) via `DB_LinkTableMapper`.

- **`DB_ProjectMapper`**    — Maps `API_Project` records along with collaborator, follower, and member profile link tables.

- **`DB_RelationMapper`**   — Maps relationship edge records and modifiers (`API_Relation`) connecting profiles and unions.

- **`DB_RevisionMapper`**   — Maps `API_Revision` edit/audit history entries.

- **`DB_SurnameMapper`**    — Maps `API_Surname` records and aggregate surname statistics.

- **`DB_UnionMapper`**      — Maps `API_Union` family unit records and their partner/child associations.

- **`DB_UserMapper`**       — Maps `API_User` account records, custom app metadata, and user-level followed/managed/uploaded collections.

### Media Mappers

- **`DB_DocumentMapper`**   — Maps `API_Document` records, dates, image size maps, and tagged profiles.

- **`DB_PhotoMapper`**      — Maps `API_Photo` records, dates, image size maps, album memberships, and tagged profiles.

- **`DB_PhotoAlbumMapper`** — Maps `API_PhotoAlbum` records and contained photo associations.

- **`DB_VideoMapper`**      — Maps `API_Video` records, dates, image size maps, and tagged profiles.

---

## 3. Mapper Core (`ShmuelAKam\Greenjeans\Persistence\Mappers\Core`)

Foundational classes shared across the mapper hierarchy:

- **`DB_BaseMapper`**      — Abstract root mapper encapsulating `PDO` and `run_id` scoping, pre-prepared primary CRUD statements (`exists()`, `readArray()`, `deleteById()`), cached dynamic column queries (`readAllBy()`, `readColBy()`, `deleteBy()`, `countBy()`, `countTableRows()`), automatic PDO type inference (`executeWithBinds()`), column-name remapping (`mapColumns()`), and ASCII table debugging (`dumpTable()`).

- **`DB_LinkTableMapper`** — Extends `DB_BaseMapper` to manage all many-to-many junction/link tables (`save()`, `saveSequence()`, `readCol()`, `readRow()`, `clear()`). Caches `PDOStatement` instances by structural fingerprint (`type_table_columns_order`), accepts string IDs, key-maps, or `getID()` objects, supports ordered sequence columns, and enforces deterministic `ORDER BY` reads scoped by `run_id`.

---

## 4. Child PODO Mappers (`ShmuelAKam\Greenjeans\Persistence\Mappers\Data`)

Specialized mappers responsible for persisting and hydrating passive `Api\Data` sub-objects and attached metadata rows owned by parent entities. These mappers are invoked internally by top-level mappers rather than called directly:

### Genealogical & Contact Sub-Object Mappers

- **`DB_NameMapper`**       — Persists and hydrates localized `API_Name` records attached to a profile.

- **`DB_EventMapper`**      — Persists and hydrates `API_Event` life events (`birth`, `death`, `baptism`, `burial`, `marriage`, `divorce`), coordinating nested `DB_DateMapper` and `DB_LocationMapper` calls.

- **`DB_DateMapper`**       — Persists and hydrates sparse `API_Date` structures (including `circa` flags and `before`/`after`/`between` range bounds).

- **`DB_LocationMapper`**   — Persists and hydrates structured `API_Location` geographic records.

- **`DB_PhoneNumberMapper`**— Persists and hydrates `API_PhoneNumber` contact records attached to profiles.

- **`DB_CommentMapper`**    — Persists and hydrates `API_Comment` records attached to profiles, projects, or media.

- **`DB_TagMapper`**        — Persists and hydrates media tag associations linking profiles to photos, documents, or videos.

### Media Image Size Map Mappers

- **`DB_DocumentImageSizeMapMapper`** — Persists and hydrates `API_DocumentImageSizeMap` preview/thumbnail URL mappings.

- **`DB_PhotoImageSizeMapMapper`**    — Persists and hydrates `API_PhotoImageSizeMap` resolution URL mappings.

- **`DB_VideoImageSizeMapMapper`**    — Persists and hydrates `API_VideoImageSizeMap` thumbnail/frame URL mappings.