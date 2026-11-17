# `ShmuelAKam\Greenjeans\Api` Overview

The `ShmuelAKam\Greenjeans\Api` namespace tree provides an object-oriented PHP SDK for interacting with the Geni.com API. It encapsulates authentication, HTTP transport, pagination, and response hydration into typed PHP objects, and can be used completely independently of the `Persistence` database layer.

---

## Namespace Structure

```text
ShmuelAKam\Greenjeans\Api\
├── (root)   # High-level domain entities with direct Geni API endpoints
├── Auth\    # Bootstrap scripts for the three Geni OAuth flows
├── Core\    # Singleton HTTP transport wrappers, pagination lists, and API exceptions
└── Data\    # Passive child PODOs and enums without standalone API endpoints
```

---

## 1. Root Domain Entities (`ShmuelAKam\Greenjeans\Api`)

Classes in the root `Api` namespace represent primary Geni resources that map directly to remote API endpoints and expose business and CRUD operations.

### Base Abstractions

- **`API_Base`**        — Concrete root class shared by all top-level API entities. Tracks lazy-loaded sub-collection states (`isLoaded()`), performs single-field API lookups via `API_Wrapper`, and provides Geni-specific formatting helpers (converting ID arrays to comma-separated/GUID strings and normalizing inconsistent comma-delimited API responses).
- **`API_MediaBase`**   — Extends `API_Base` to provide shared upload, metadata, and tagging behavior across all media types (`API_Document`, `API_Photo`, and `API_Video`). Implements most of the API for the derived classes.

### Genealogical & Account Entities

- **`API_Profile`**     — The central genealogical entity representing an individual in the Geni world tree. Supports full or lightweight retrieval (`get()`, `getBrief()`, `getProfileSelf()`), lazy on-demand field loading, dirty-tracked updates (`set*()` + `update()`), relative creation (`addChild()`, `addParent()`, `addPartner()`, `addSibling()`), media uploads, relationship pathfinding (`getPathTo()`), profile merging/comparison, and static search/ID-GUID conversion (`search()`, `convertID()`).

> **Core Mechanics (`API_Profile`):**
> - **Lazy Field Loading:** Profiles fetched via `getBrief()` initially populate only `id`, `is_alive`, `name`, and `public`. Calling any unloaded getter automatically fetches that field on demand (pass `$force = true` to refresh cached values).
> - **Deferred / Dirty-Tracked Writes:** `set*()` methods modify only the in-memory `$values` array and flag the field as dirty; changes are sent to Geni only when `update()` or an `add*()` relative-creation method is called.
> - **Composed PODOs & Collections:** Hydrates nested `Api\Data` objects (`birth`, `death`, `baptism`, `burial`, `events`, `names`, `current_residence`, `phone_numbers`, `mugshot_urls`) and exposes lazy `API_ListObjects` collections for `documents`, `photos`, and `videos`.

- **`API_Project`**     — Represents a collaborative Geni project (`get()`, `verifyProjectExists()`), exposing lazy-loaded `API_ListProfiles` collections (`getCollaborators()`, `getFollowers()`, `getProfiles()`), lightweight member counts (`getNumProfiles()`), and chunked batch additions (`addProfiles()`, `addDocuments()` in blocks of 50, reporting added counts and rejected IDs).
- **`API_Revision`**    — Represents historical edit/audit log entries on Geni objects.
- **`API_Stats`**       — Exposes tree and account statistics endpoints.
- **`API_Surname`**     — Provides surname-level queries and aggregate data across the tree.
- **`API_Union`**       — Represents a family unit/partnership connecting partners and their children.
- **`API_User`**        — Mostly read-only representation of the authenticated Geni user (`get()`), exposing `name`, `accountType` (`basic`, `plus`, `pro`), mutable custom app `metadata` (`getMetadata()` / `updateMetadata()`), lightweight list counts (`getListCount()`), and 11 lazy-loaded paginated collections for followed items (`followed-profiles`, `followed-projects`, `followed-documents`, `followed-surnames`), tree scope (`managed-profiles`, `max-family`), and user uploads/albums/labels.

### Media Entities
- **`API_Document`**    — Manages uploaded genealogical documents (extends `API_MediaBase`).
- **`API_Photo`**       — Manages individual photos and image size maps (extends `API_MediaBase`).
- **`API_PhotoAlbum`**  — Groups and manages collections of `API_Photo` objects.
- **`API_Video`**       — Manages video uploads and metadata (extends `API_MediaBase`).

---

## 2. Authentication (`ShmuelAKam\Greenjeans\Api\Auth`)

Contains bootstrap scripts demonstrating how to authenticate against Geni's three supported OAuth flows and initialize the corresponding wrapper from `Api\Core`. Copy the script matching your application type and adjust `PACKAGE_ROOT`:

- **`testClient.php`**  — Client-side / implicit OAuth authentication flow (pairs with `API_WrapperClient`).
- **`testDesktop.php`** — Desktop / installed-application OAuth authentication flow (pairs with `API_WrapperDesktop`).
- **`testServer.php`**  — Server-side / authorization-code OAuth authentication flow (pairs with `API_WrapperServer`).

---

## 3. Transport, Pagination & Exceptions (`ShmuelAKam\Greenjeans\Api\Core`)

The internal engine powering HTTP requests, OAuth token handling, and multi-page result traversal.

### Transport Wrappers

`API_Wrapper` operates as a **Singleton**. After a one-time initialization during application startup (typically handled by the scripts in `Api\Auth`), end users never see, pass, or interact with these wrapper instances directly—all root entities retrieve the active instance internally via `API_Wrapper::getInstance()`:

- **`API_Wrapper`**        — Singleton HTTP client base managing request execution, JSON decoding, rate-limit handling, and error checking.
- **`API_WrapperClient`**  — Wrapper specialization for client-side OAuth tokens.
- **`API_WrapperDesktop`** — Wrapper specialization for desktop application OAuth tokens.
- **`API_WrapperServer`**  — Wrapper specialization for server-side OAuth tokens.

### Paginated Lists

Read-only collection classes implementing PHP's `ArrayAccess`, `Countable`, and `IteratorAggregate` interfaces so they can be iterated with `foreach`, counted with `count()`, and indexed like standard arrays. Depending on how they are constructed, lists either fetch **all pages upfront** (`$getAll = true`) or load a single page (default batch size: `50`) and expose explicit `nextPage()` / `previousPage()` navigation:

- **`API_ListBase`**     — Abstract engine handling pagination state, HTTP page requests, response unwrapping (`results` vs. single-item replies), ID-only lightweight queries (`$IDsOnly`), and static count checks (`getListCount()`). Subclasses implement `parseArray()` to hydrate specific item types.
- **`API_ListObjects`**  — Paginated collection of hydrated `API_Base` domain entities (keyed by object ID).
- **`API_ListProfiles`** — Specialized paginated collection for `API_Profile` result sets (supporting both full profile hydration and ID/GUID-only modes).
- **`API_ListStrings`**  — Paginated collection for endpoints returning scalar string lists (such as tags or nicknames).

### Error Handling

- **`API_Exception`**    — Custom exception thrown on HTTP transport errors, invalid OAuth tokens, rate-limit violations, or malformed API responses.

---

## 4. Child PODOs & Enums (`ShmuelAKam\Greenjeans\Api\Data`)

Lightweight Plain Old Data Objects and enums representing structured sub-components composed inside root `ShmuelAKam\Greenjeans\Api` entities. These types **do not have their own API endpoints** and cannot be fetched or saved independently—they exist solely as properties or collections on parent objects.

### Genealogical & Contact Sub-Objects
- **`API_Name`**        — Structured personal name components (first, middle, last, maiden, suffix, display name).
- **`API_Gender`**      — PHP enum representing standardized gender values on an `API_Profile`.
- **`API_Event`**       — Life events (birth, baptism, marriage, death, burial) composed within profiles or unions.
- **`API_Date`**        — Sparse-array genealogical date object used by `API_Event` and media entities. Preserves partial/incomplete dates (storing only defined fields), supports `circa` flags, date ranges (`before`, `after`, `between`), historical/BCE years, normalized chronological sort keys (`getSortKey()`), and conversions between Epoch, `d/m/y` timestamps, and JSON formats.
- **`API_Location`**    — Structured geographic place details (city, county, state, country, coordinates) attached to events or profiles.
- **`API_PhoneNumber`** — Structured telephone contact details attached to a profile or user.
- **`API_Relation`**    — Describes relationship edge metadata (such as partner/child relationship modifiers) between profiles and unions.
- **`API_Path`**        — Represents a genealogical relationship path/chain connecting two profiles.
- **`API_Comment`**     — User comments attached to profiles, projects, or media items.

### Media Image Size Maps
- **`API_DocumentImageSizeMap`** — Maps available preview/thumbnail dimensions and URLs for an `API_Document`.
- **`API_PhotoImageSizeMap`**    — Maps available resolution variants and URLs for an `API_Photo`.
- **`API_VideoImageSizeMap`**    — Maps available thumbnail/frame dimensions and URLs for an `API_Video`.
