-- ====================================================================================================
-- 1. INFRASTRUCTURE
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS geni_clients (
    client_id       INT AUTO_INCREMENT NOT NULL,
    username        VARCHAR(20) NOT NULL UNIQUE,
    profile_id      VARCHAR(50) NOT NULL,
    guid            VARCHAR(50) NOT NULL,
    name            VARCHAR(255),

    PRIMARY KEY (client_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_runs (
    run_id          INT AUTO_INCREMENT NOT NULL, 
    client_id       INT NOT NULL,     
    start_time      INT NOT NULL,         
    duration_s      INT DEFAULT 0,    
    memory_alloc_mb INT DEFAULT 0,
    run_fail        BOOLEAN DEFAULT FALSE,
    run_complete    BOOLEAN DEFAULT FALSE,

    PRIMARY KEY (run_id),
    FOREIGN KEY (client_id) REFERENCES geni_clients(client_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- 2. PRIMITIVE DATA STRUCTURES (Reusable Components)
-- ==============================================================

CREATE TABLE IF NOT EXISTS geni_comments (
    id                  INT AUTO_INCREMENT,
    owner_id            VARCHAR(50),
    comment             TEXT,
    created_at          BIGINT,
    title               VARCHAR(255),
    run_id              INT NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ALL data fields are optional.

CREATE TABLE IF NOT EXISTS geni_dates (
    id                  INT AUTO_INCREMENT,
    owner_id            VARCHAR(50),
    circa               BOOLEAN DEFAULT FALSE,
    day                 INT DEFAULT NULL,
    month               INT DEFAULT NULL,
    year                INT DEFAULT NULL,
    end_circa           BOOLEAN DEFAULT FALSE,
    end_day             INT DEFAULT NULL,
    end_month           INT DEFAULT NULL,
    end_year            INT DEFAULT NULL,
    date_range          VARCHAR(10) DEFAULT NULL, -- maps to 'range' field in API.
    run_id              INT NOT NULL,
    
    PRIMARY KEY (id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_locations (
    id                  INT AUTO_INCREMENT,
    owner_id            VARCHAR(50),
    country             VARCHAR(100),
    county              VARCHAR(100),
    city                VARCHAR(100),
    place_name          VARCHAR(255),
    state               VARCHAR(100),
    street_address1     VARCHAR(255),
    street_address2     VARCHAR(255),
    street_address3     VARCHAR(255),
    longitude           DECIMAL(11, 8),
    latitude            DECIMAL(10, 8),
    formatted_location  TEXT,
    country_code        VARCHAR(10),
    run_id              INT NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_events (
    id                  INT AUTO_INCREMENT,
    owner_id            VARCHAR(50),
    name                VARCHAR(255) NOT NULL,
    description         TEXT,
    
    date_id             INT,
    location_id         INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_names (
    id                  INT AUTO_INCREMENT,
    owner_id            VARCHAR(50),
    display_name        VARCHAR(255),
    first_name          VARCHAR(100),
    last_name           VARCHAR(100),
    maiden_name         VARCHAR(100),
    middle_name         VARCHAR(100),
    suffix              VARCHAR(20),
    nicknames           TEXT,
    title               VARCHAR(100),
    run_id              INT NOT NULL,
    
    PRIMARY KEY (id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_phone_numbers (
    id                  INT AUTO_INCREMENT,
    owner_id            VARCHAR(50),
    area_code           VARCHAR(10),
    country_code        VARCHAR(10),
    number              VARCHAR(30),
    type                VARCHAR(20),
    run_id              INT NOT NULL,

    PRIMARY KEY (id),
    INDEX idx_owner (owner_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_relations (
    id             INT AUTO_INCREMENT NOT NULL,
    parent_id      VARCHAR(50) NOT NULL, -- The owner Profile
    sequence_order INT NOT NULL,         -- 1, 2, 3...
    
    name           VARCHAR(255),
    relation_type  VARCHAR(100),
    url            TEXT,
    run_id         INT NOT NULL,

    PRIMARY KEY (id),
    
    /* Optimized for: findByParentId() */
    INDEX idx_parent_run (parent_id, run_id),
    
    /* Standard cleanup index */
    INDEX idx_run (run_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ====================================================================================================
-- 2. MEDIA
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS geni_documents (
    doc_id              VARCHAR(50) NOT NULL,
    title               VARCHAR(255),
    description         TEXT,
    text_content        LONGTEXT,
    attribution         VARCHAR(255),
    content_type        VARCHAR(100),
    guid                VARCHAR(50),
    page_count          INT,
    source_url          VARCHAR(255),
    url                 VARCHAR(255),
    
    date_id             INT,
    created_at          BIGINT,
    updated_at          BIGINT,
    location_id         INT,
    size_id             INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (doc_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_photos (
    photo_id            VARCHAR(50),
    album_id            VARCHAR(50),
    title               VARCHAR(255) NOT NULL,
    description         TEXT,
    attribution         VARCHAR(255),
    content_type        VARCHAR(100),
    guid                VARCHAR(50),
    url                 VARCHAR(255),
    created_at          BIGINT,
    updated_at          BIGINT,
    date_id             INT,
    location_id         INT,
    size_id             INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (photo_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_videos (
    video_id            VARCHAR(50),
    title               VARCHAR(255),
    description         TEXT,
    attribution         VARCHAR(255),
    content_type        VARCHAR(100),
    guid                VARCHAR(50),
    url                 VARCHAR(255),
    created_at          BIGINT,
    updated_at          BIGINT,
    date_id             INT,
    location_id         INT,
    size_id             INT,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (video_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_photo_albums (
    album_id            VARCHAR(50),
    name                VARCHAR(255),
    description         TEXT,
    photos_count        INT,
    url                 VARCHAR(255),
    cover_sizes_id      INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (album_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ====================================================================================================
-- 3. PRIMARY OBJECTS (CORE DATA)
-- ====================================================================================================

-- ==============================================================
-- PROFILES

CREATE TABLE IF NOT EXISTS geni_profiles (
    profile_id           VARCHAR(50),
    guid                 VARCHAR(50),
    name                 VARCHAR(255),
    display_name         VARCHAR(255),
    first_name           VARCHAR(100),
    middle_name          VARCHAR(100),
    last_name            VARCHAR(100),
    maiden_name          VARCHAR(100),
    suffix               VARCHAR(20),
    gender               VARCHAR(10),
    is_alive             TINYINT(1),
    public               TINYINT(1),
    claimed              TINYINT(1),
    big_tree             TINYINT(1),
    locked               TINYINT(1),
    master_profile       TINYINT(1),
    merge_pending        TINYINT(1),
    occupation           VARCHAR(255),
    email                VARCHAR(255),
    get_email            TINYINT(1),
    language             VARCHAR(10),
    about_me             LONGTEXT,
    cause_of_death       VARCHAR(255),
    merge_note           TEXT,
    birth_id             INT,
    death_id             INT,
    burial_id            INT,
    baptism_id           INT,
    residence_id         INT,
    mugshot_id           INT,
    location_id          INT,
    
    created_at           BIGINT,
    updated_at           BIGINT,
    documents_updated_at VARCHAR(100),
    photos_updated_at    VARCHAR(100),
    videos_updated_at    VARCHAR(100),
    creator_id           VARCHAR(50),
    curator_id           VARCHAR(50),
    merged_into_id       VARCHAR(50),
    nicknames            TEXT,
    run_id               INT NOT NULL,
    
    PRIMARY KEY (profile_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- UNIONS

CREATE TABLE IF NOT EXISTS geni_unions (
    union_id            VARCHAR(50),
    status              VARCHAR(20),
    marriage_id         INT,
    divorce_id          INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (union_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_union_partners (
    union_id            VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (union_id, profile_id), -- Composite PK
    FOREIGN KEY (run_id)   REFERENCES geni_runs  (run_id)   ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_union_children (
    union_id            VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    child_type          ENUM('biological', 'adopted', 'foster') DEFAULT 'biological',
    run_id              INT NOT NULL,

    PRIMARY KEY (union_id, profile_id), -- Composite PK
    FOREIGN KEY (run_id)   REFERENCES geni_runs  (run_id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================================================
-- PROJECTS

CREATE TABLE IF NOT EXISTS geni_projects (
    project_id          VARCHAR(50),
    name                VARCHAR(255),
    description         TEXT,
    created_at          BIGINT,
    updated_at          BIGINT,
    url                 VARCHAR(255),
    run_id              INT NOT NULL,

    PRIMARY KEY (project_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_surnames (
    surname_id          VARCHAR(50),
    slugged_name        VARCHAR(255),
    description         TEXT,
    url                 VARCHAR(255),
    follower_count      INT,
    profile_count       INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (surname_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_users (
    user_id             INT NOT NULL AUTO_INCREMENT,
    name                VARCHAR(255),
    account_type        VARCHAR(50),
    metadata_json       TEXT,
    run_id              INT NOT NULL UNIQUE,
    
    PRIMARY KEY (user_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_revisions (
    revision_id         VARCHAR(50),
    guid                VARCHAR(50),
    action              VARCHAR(100),
    story               TEXT,
    date_local          VARCHAR(50),
    time_local          VARCHAR(50),
    server_timestamp    VARCHAR(50),
    run_id              INT NOT NULL,

    PRIMARY KEY (revision_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ====================================================================================================
-- 4. LINK TABLES (COLLECTIONS)
-- ====================================================================================================

CREATE TABLE IF NOT EXISTS geni_profile_names_localized (
    id                  INT AUTO_INCREMENT,
    profile_id          VARCHAR(50) NOT NULL, -- The owner
    locale              VARCHAR(10) NOT NULL, -- 'en', 'he', 'ru', etc.
    
    display_name        VARCHAR(255),
    first_name          VARCHAR(100),
    last_name           VARCHAR(100),
    maiden_name         VARCHAR(100),
    middle_name         VARCHAR(100),
    suffix              VARCHAR(20),
    nicknames           TEXT,
    title               VARCHAR(100),
    
    run_id              INT NOT NULL,
    
    PRIMARY KEY (id),
    UNIQUE KEY uk_profile_locale (profile_id, locale, run_id),
    
    INDEX idx_run (run_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS geni_profile_projects (
    profile_id          VARCHAR(50) NOT NULL,
    project_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, project_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_unions (
    profile_id          VARCHAR(50) NOT NULL,
    union_id            VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, union_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_managers (
    profile_id          VARCHAR(50) NOT NULL,
    manager_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, manager_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_req_merges (
    profile_id          VARCHAR(50) NOT NULL,
    other_id            VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, other_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_details (
    profile_id          VARCHAR(50) NOT NULL,
    locale              VARCHAR(10) NOT NULL,
    detail_strings      LONGTEXT,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, locale),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_documents (
    profile_id          VARCHAR(50) NOT NULL,
    doc_id              VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, doc_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_photos (
    profile_id          VARCHAR(50) NOT NULL,
    photo_id            VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, photo_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_profile_videos (
    profile_id          VARCHAR(50) NOT NULL,
    video_id            VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (profile_id, video_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs(run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;


-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS geni_project_profiles (
    project_id          VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (project_id, profile_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs    (run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_project_collaborators (
    project_id          VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (project_id, profile_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs    (run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_project_followers (
    project_id          VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (project_id, profile_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs    (run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS geni_surname_profiles (
    surname_id          VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (surname_id, profile_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs    (run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_surname_followers (
    surname_id          VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,
    
    PRIMARY KEY (surname_id, profile_id),
    FOREIGN KEY (run_id)     REFERENCES geni_runs    (run_id)     ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------

CREATE TABLE IF NOT EXISTS geni_user_followed_documents (
    user_id             INT NOT NULL,
    doc_id              VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, doc_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_followed_profiles (
    user_id             INT NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, profile_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_followed_projects (
    user_id             INT NOT NULL,
    project_id          INT NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, project_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_followed_surnames (
    user_id             INT NOT NULL,
    surname_id          INT NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, surname_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_managed_profiles (
    user_id             INT NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, profile_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_max_family (
    user_id             INT NOT NULL,
    profile_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, profile_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_my_albums (
    user_id             INT NOT NULL,
    album_id            INT NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, album_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_uploaded_documents (
    user_id             INT NOT NULL,
    doc_id              VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, doc_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_uploaded_photos (
    user_id             INT NOT NULL,
    photo_id            INT NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, photo_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_uploaded_videos (
    user_id             INT NOT NULL,
    video_id            INT NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, video_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_user_labels (
    user_id             INT NOT NULL,
    label               VARCHAR(100) NOT NULL,
    insert_order        INT,
    run_id              INT NOT NULL,

    PRIMARY KEY (user_id, label),
    FOREIGN KEY (run_id) REFERENCES geni_runs (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ====================================================================================================
-- 5. Media sub-tables.
-- ====================================================================================================

-- ---------------------------------------------------------
-- Tags sub-table for all media types (documents, photo, and video)
-- Having a single table enables searching for ALL media that a profile is in.

CREATE TABLE IF NOT EXISTS geni_media_tags (
    owner_id            VARCHAR(50) NOT NULL,
    profile_id          VARCHAR(50),
    run_id              INT NOT NULL,

    PRIMARY KEY (owner_id, profile_id),
    FOREIGN KEY (run_id)   REFERENCES geni_runs   (run_id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Document sub-tables.

CREATE TABLE IF NOT EXISTS geni_document_labels (
    doc_id              VARCHAR(50)  NOT NULL,
    label               VARCHAR(100) NOT NULL,
    insert_order        INT,
    run_id              INT NOT NULL,

    INDEX idx_doc (doc_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs     (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_document_projects (
    doc_id              VARCHAR(50) NOT NULL,
    project_id          VARCHAR(50) NOT NULL,
    run_id              INT NOT NULL,

    PRIMARY KEY (doc_id, project_id),
    INDEX idx_project (project_id),
    FOREIGN KEY (run_id) REFERENCES geni_runs      (run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS geni_document_sizes (
    id                  INT AUTO_INCREMENT,
    url_large           VARCHAR(255),
    url_origin          VARCHAR(255),
    url_thumb           VARCHAR(255),
    run_id              INT NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Photo sub-tables.

CREATE TABLE IF NOT EXISTS geni_photo_sizes (
    id                  INT AUTO_INCREMENT,
    url_large           VARCHAR(255),
    url_medium          VARCHAR(255),
    url_origin          VARCHAR(255),
    url_print           VARCHAR(255),
    url_small           VARCHAR(255),
    url_thumb           VARCHAR(255),
    url_thumb2          VARCHAR(255),
    run_id              INT NOT NULL,
    
    PRIMARY KEY (id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Video sub-tables.

CREATE TABLE IF NOT EXISTS geni_video_sizes (
    id                  INT AUTO_INCREMENT,
    url_large           VARCHAR(255),
    url_medium          VARCHAR(255),
    url_origin          VARCHAR(255),
    url_print           VARCHAR(255),
    url_small           VARCHAR(255),
    url_thumb           VARCHAR(255),
    url_thumb2          VARCHAR(255),
    run_id              INT NOT NULL,
    
    PRIMARY KEY (id),
    FOREIGN KEY (run_id) REFERENCES geni_runs(run_id) ON DELETE CASCADE
) ENGINE=InnoDB;
