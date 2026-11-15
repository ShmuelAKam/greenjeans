-- ====================================================================================================
-- 1. INFRASTRUCTURE
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS "geni_clients" (
    "client_id"       INTEGER PRIMARY KEY AUTOINCREMENT,
    "username"        VARCHAR(20) NOT NULL UNIQUE,
    "profile_id"      VARCHAR(50) NOT NULL,
    "guid"            TEXT NOT NULL,
    "name"            TEXT
);

CREATE TABLE IF NOT EXISTS "geni_runs" (
    "run_id"          INTEGER PRIMARY KEY AUTOINCREMENT,
    "client_id"       INTEGER NOT NULL,
    "start_time"      INTEGER NOT NULL,
    "duration_s"      INTEGER DEFAULT 0,
    "memory_alloc_mb" INTEGER DEFAULT 0,
    "run_fail"        INTEGER DEFAULT 0,
    "run_complete"    INTEGER DEFAULT 0,

    FOREIGN KEY ("client_id") REFERENCES "geni_clients"("client_id") ON DELETE CASCADE
);

-- ==============================================================
-- 2. PRIMITIVE DATA STRUCTURES (Reusable Components)
-- ==============================================================

CREATE TABLE IF NOT EXISTS "geni_comments" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "owner_id"        TEXT,
    "comment"         TEXT,
    "created_at"      TEXT,
    "title"           TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_comments_owner" ON "geni_comments" ("owner_id");

-- ALL data fields are optional.

CREATE TABLE IF NOT EXISTS "geni_dates" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "owner_id"        TEXT,
    "circa"           INTEGER DEFAULT NULL,
    "day"             INTEGER DEFAULT NULL,
    "month"           INTEGER DEFAULT NULL,
    "year"            INTEGER DEFAULT NULL,
    "end_circa"       INTEGER DEFAULT NULL,
    "end_day"         INTEGER DEFAULT NULL,
    "end_month"       INTEGER DEFAULT NULL,
    "end_year"        INTEGER DEFAULT NULL,
    "date_range"      TEXT    DEFAULT NULL, -- maps to 'range' field in API.
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_dates_owner" ON "geni_dates" ("owner_id");

CREATE TABLE IF NOT EXISTS "geni_locations" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "owner_id"        TEXT,
    "country"         TEXT,
    "county"          TEXT,
    "city"            TEXT,
    "place_name"      TEXT,
    "state"           TEXT,
    "street_address1" TEXT,
    "street_address2" TEXT,
    "street_address3" TEXT,
    "longitude"       REAL,
    "latitude"        REAL,
    "formatted_location" TEXT,
    "country_code"    TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_locations_owner" ON "geni_locations" ("owner_id");

CREATE TABLE IF NOT EXISTS "geni_events" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "owner_id"        TEXT,
    "name"            TEXT NOT NULL,
    "description"     TEXT,
    "date_id"         INTEGER,
    "location_id"     INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_events_owner" ON "geni_events" ("owner_id");

CREATE TABLE IF NOT EXISTS "geni_names" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "owner_id"        TEXT,
    "display_name"    TEXT,
    "first_name"      TEXT,
    "last_name"       TEXT,
    "maiden_name"     TEXT,
    "middle_name"     TEXT,
    "suffix"          TEXT,
    "nicknames"       TEXT,
    "title"           TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_names_owner" ON "geni_names" ("owner_id");

CREATE TABLE IF NOT EXISTS "geni_phone_numbers" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "owner_id"        TEXT,
    "area_code"       TEXT,
    "country_code"    TEXT,
    "number"          TEXT,
    "type"            TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_phone_numbers_owner" ON "geni_phone_numbers" ("owner_id");

CREATE TABLE IF NOT EXISTS "geni_relations" (
    "id"             INTEGER PRIMARY KEY AUTOINCREMENT,
    "parent_id"      TEXT NOT NULL, -- The owner Profile
    "sequence_order" INTEGER NOT NULL, -- 1, 2, 3...
    "name"           TEXT,
    "relation_type"  TEXT,
    "url"            TEXT,
    "run_id"         INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs" ("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_relations_parent_run" ON "geni_relations" ("parent_id", "run_id");
CREATE INDEX IF NOT EXISTS "idx_relations_run" ON "geni_relations" ("run_id");

-- ====================================================================================================
-- 3. MEDIA
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS "geni_documents" (
    "doc_id"          TEXT PRIMARY KEY,
    "title"           TEXT,
    "description"     TEXT,
    "text_content"    TEXT,
    "attribution"     TEXT,
    "content_type"    TEXT,
    "guid"            TEXT,
    "page_count"      INTEGER,
    "source_url"      TEXT,
    "url"             TEXT,
    "date_id"         INTEGER,
    "created_at"      TEXT,
    "updated_at"      TEXT,
    "location_id"     INTEGER,
    "size_id"         INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_photos" (
    "photo_id"        TEXT PRIMARY KEY,
    "album_id"        TEXT,
    "title"           TEXT NOT NULL,
    "description"     TEXT,
    "attribution"     TEXT,
    "content_type"    TEXT,
    "guid"            TEXT,
    "url"             TEXT,
    "created_at"      TEXT,
    "updated_at"      TEXT,
    "date_id"         INTEGER,
    "location_id"     INTEGER,
    "size_id"         INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_videos" (
    "video_id"        TEXT PRIMARY KEY,
    "title"           TEXT,
    "description"     TEXT,
    "attribution"     TEXT,
    "content_type"    TEXT,
    "guid"            TEXT,
    "url"             TEXT,
    "created_at"      TEXT,
    "updated_at"      TEXT,
    "date_id"         INTEGER,
    "location_id"     INTEGER,
    "size_id"         INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_photo_albums" (
    "album_id"        TEXT PRIMARY KEY,
    "name"            TEXT,
    "description"     TEXT,
    "photos_count"    INTEGER,
    "url"             TEXT,
    "cover_sizes_id"  INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

-- ====================================================================================================
-- 4. PRIMARY OBJECTS (CORE DATA)
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS "geni_profiles" (
    "profile_id"           TEXT PRIMARY KEY,
    "guid"                 TEXT,
    "name"                 TEXT,
    "display_name"         TEXT,
    "first_name"           TEXT,
    "middle_name"          TEXT,
    "last_name"            TEXT,
    "maiden_name"          TEXT,
    "suffix"               TEXT,
    "gender"               TEXT,
    "is_alive"             INTEGER,
    "public"               INTEGER,
    "claimed"              INTEGER,
    "big_tree"             INTEGER,
    "locked"               INTEGER,
    "master_profile"       INTEGER,
    "merge_pending"        INTEGER,
    "occupation"           TEXT,
    "email"                TEXT,
    "get_email"            INTEGER,
    "language"             TEXT,
    "about_me"             TEXT,
    "cause_of_death"       TEXT,
    "merge_note"           TEXT,
    "birth_id"             INTEGER,
    "death_id"             INTEGER,
    "burial_id"            INTEGER,
    "baptism_id"           INTEGER,
    "residence_id"         INTEGER,
    "mugshot_id"           INTEGER,
    "location_id"          INTEGER,
    "created_at"           TEXT,
    "updated_at"           TEXT,
    "documents_updated_at" TEXT,
    "photos_updated_at"    TEXT,
    "videos_updated_at"    TEXT,
    "creator_id"           TEXT,
    "curator_id"           TEXT,
    "merged_into_id"       TEXT,
    "nicknames"            TEXT,
    "run_id"               INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_unions" (
    "union_id"        TEXT PRIMARY KEY,
    "status"          TEXT,
    "marriage_id"     INTEGER,
    "divorce_id"      INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_union_partners" (
    "union_id"        TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("union_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_union_children" (
    "union_id"        TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "child_type"      TEXT DEFAULT 'biological' CHECK("child_type" IN ('biological', 'adopted', 'foster')),
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("union_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_projects" (
    "project_id"      TEXT PRIMARY KEY,
    "name"            TEXT,
    "description"     TEXT,
    "created_at"      TEXT,
    "updated_at"      TEXT,
    "url"             TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_surnames" (
    "surname_id"      TEXT PRIMARY KEY,
    "slugged_name"    TEXT,
    "description"     TEXT,
    "url"             TEXT,
    "follower_count"  INTEGER,
    "profile_count"   INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_users" (
    "user_id"         INTEGER PRIMARY KEY AUTOINCREMENT,
    "name"            TEXT,
    "account_type"    TEXT,
    "metadata_json"   TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_revisions" (
    "revision_id"      TEXT PRIMARY KEY,
    "guid"             TEXT,
    "action"           TEXT,
    "story"            TEXT,
    "date_local"       TEXT,
    "time_local"       TEXT,
    "server_timestamp" TEXT,
    "run_id"           INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

-- ====================================================================================================
-- 5. LINK TABLES (COLLECTIONS)
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS "geni_profile_names_localized" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "profile_id"      TEXT NOT NULL,
    "locale"          TEXT NOT NULL,
    "display_name"    TEXT,
    "first_name"      TEXT,
    "last_name"       TEXT,
    "maiden_name"     TEXT,
    "middle_name"     TEXT,
    "suffix"          TEXT,
    "nicknames"       TEXT,
    "title"           TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE UNIQUE INDEX IF NOT EXISTS "uk_geni_profile_names_localized_profile_locale_run" ON "geni_profile_names_localized" ("profile_id", "locale", "run_id");
CREATE INDEX IF NOT EXISTS "idx_profile_names_run" ON "geni_profile_names_localized" ("run_id");

CREATE TABLE IF NOT EXISTS "geni_profile_projects" (
    "profile_id"      TEXT NOT NULL,
    "project_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "project_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_unions" (
    "profile_id"      TEXT NOT NULL,
    "union_id"        TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "union_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_managers" (
    "profile_id"      TEXT NOT NULL,
    "manager_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "manager_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_req_merges" (
    "profile_id"      TEXT NOT NULL,
    "other_id"        TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "other_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_details" (
    "profile_id"      TEXT NOT NULL,
    "locale"          TEXT NOT NULL,
    "detail_strings"  TEXT,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "locale"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_documents" (
    "profile_id"      TEXT NOT NULL,
    "doc_id"          TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "doc_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_photos" (
    "profile_id"      TEXT NOT NULL,
    "photo_id"        TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "photo_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_profile_videos" (
    "profile_id"      TEXT NOT NULL,
    "video_id"        TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("profile_id", "video_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_project_profiles" (
    "project_id"      TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("project_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_project_collaborators" (
    "project_id"      TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("project_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_project_followers" (
    "project_id"      TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("project_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_surname_profiles" (
    "surname_id"      TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("surname_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_surname_followers" (
    "surname_id"      TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("surname_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

-- ================================================================================
-- User collection link tables.

CREATE TABLE IF NOT EXISTS "geni_user_followed_documents" (
    "user_id"         INTEGER NOT NULL,
    "doc_id"          INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "doc_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_followed_profiles" (
    "user_id"         INTEGER NOT NULL,
    "profile_id"      INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_followed_projects" (
    "user_id"         INTEGER NOT NULL,
    "project_id"      INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "project_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_followed_surnames" (
    "user_id"         INTEGER NOT NULL,
    "surname_id"      INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "surname_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_managed_profiles" (
    "user_id"         INTEGER NOT NULL,
    "profile_id"      INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_max_family" (
    "user_id"         INTEGER NOT NULL,
    "profile_id"      INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_my_albums" (
    "user_id"         INTEGER NOT NULL,
    "album_id"        INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "album_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_uploaded_documents" (
    "user_id"         INTEGER NOT NULL,
    "doc_id"          INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "doc_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_uploaded_photos" (
    "user_id"         INTEGER NOT NULL,
    "photo_id"        INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "photo_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_uploaded_videos" (
    "user_id"         INTEGER NOT NULL,
    "video_id"        INTEGER NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "video_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_user_labels" (
    "user_id"         INTEGER NOT NULL,
    "label"           TEXT    NOT NULL,
    "insert_order"    INTEGER,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("user_id", "label"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

-- ====================================================================================================
-- 6. MEDIA SUB-TABLES
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS "geni_media_tags" (
    "owner_id"        TEXT NOT NULL,
    "profile_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("owner_id", "profile_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_document_labels" (
    "doc_id"          TEXT NOT NULL,
    "label"           TEXT NOT NULL,
    "insert_order"    INTEGER,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_doc_labels_doc" ON "geni_document_labels" ("doc_id");

CREATE TABLE IF NOT EXISTS "geni_document_projects" (
    "doc_id"          TEXT NOT NULL,
    "project_id"      TEXT NOT NULL,
    "run_id"          INTEGER NOT NULL,

    PRIMARY KEY ("doc_id", "project_id"),
    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS "idx_doc_projects_project" ON "geni_document_projects" ("project_id");

CREATE TABLE IF NOT EXISTS "geni_document_sizes" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "url_large"       TEXT,
    "url_origin"      TEXT,
    "url_thumb"       TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_photo_sizes" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "url_large"       TEXT,
    "url_medium"      TEXT,
    "url_origin"      TEXT,
    "url_print"       TEXT,
    "url_small"       TEXT,
    "url_thumb"       TEXT,
    "url_thumb2"      TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS "geni_video_sizes" (
    "id"              INTEGER PRIMARY KEY AUTOINCREMENT,
    "url_large"       TEXT,
    "url_medium"      TEXT,
    "url_origin"      TEXT,
    "url_print"       TEXT,
    "url_small"       TEXT,
    "url_thumb"       TEXT,
    "url_thumb2"      TEXT,
    "run_id"          INTEGER NOT NULL,

    FOREIGN KEY ("run_id") REFERENCES "geni_runs"("run_id") ON DELETE CASCADE
);
