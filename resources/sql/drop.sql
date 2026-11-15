
-- Disable foreign key checks to ensure a smooth drop process
SET FOREIGN_KEY_CHECKS = 0;

-- ====================================================================================================
-- DROP LINK TABLES (COLLECTIONS)
-- ====================================================================================================
DROP TABLE IF EXISTS geni_profile_names_localized;
DROP TABLE IF EXISTS geni_profile_projects;
DROP TABLE IF EXISTS geni_profile_unions;
DROP TABLE IF EXISTS geni_profile_managers;
DROP TABLE IF EXISTS geni_profile_req_merges;
DROP TABLE IF EXISTS geni_profile_details;
DROP TABLE IF EXISTS geni_profile_documents;
DROP TABLE IF EXISTS geni_profile_photos;
DROP TABLE IF EXISTS geni_profile_videos;

DROP TABLE IF EXISTS geni_union_partners;
DROP TABLE IF EXISTS geni_union_children;
DROP TABLE IF EXISTS geni_media_tags;
DROP TABLE IF EXISTS geni_document_labels;
DROP TABLE IF EXISTS geni_document_projects;
DROP TABLE IF EXISTS geni_video_comments;
DROP TABLE IF EXISTS geni_path_relations;
DROP TABLE IF EXISTS geni_project_profiles;
DROP TABLE IF EXISTS geni_project_collaborators;
DROP TABLE IF EXISTS geni_project_followers;
DROP TABLE IF EXISTS geni_surname_followers;
DROP TABLE IF EXISTS geni_surname_profiles;
DROP TABLE IF EXISTS geni_user_followed_documents;
DROP TABLE IF EXISTS geni_user_followed_profiles;
DROP TABLE IF EXISTS geni_user_followed_projects;
DROP TABLE IF EXISTS geni_user_followed_surnames;
DROP TABLE IF EXISTS geni_user_managed_profiles;
DROP TABLE IF EXISTS geni_user_max_family;
DROP TABLE IF EXISTS geni_user_my_albums;
DROP TABLE IF EXISTS geni_user_uploaded_documents;
DROP TABLE IF EXISTS geni_user_uploaded_photos;
DROP TABLE IF EXISTS geni_user_uploaded_videos;
DROP TABLE IF EXISTS geni_user_labels;

-- ====================================================================================================
-- DROP PRIMARY OBJECTS
-- ====================================================================================================
DROP TABLE IF EXISTS geni_profiles;
DROP TABLE IF EXISTS geni_unions;
DROP TABLE IF EXISTS geni_documents;
DROP TABLE IF EXISTS geni_photos;
DROP TABLE IF EXISTS geni_videos;
DROP TABLE IF EXISTS geni_photo_albums;
DROP TABLE IF EXISTS geni_projects;
DROP TABLE IF EXISTS geni_surnames;
DROP TABLE IF EXISTS geni_users;
DROP TABLE IF EXISTS geni_revisions;

-- ====================================================================================================
-- DROP SUB-OBJECTS & INFRASTRUCTURE
-- ====================================================================================================
DROP TABLE IF EXISTS geni_events;
DROP TABLE IF EXISTS geni_locations;
DROP TABLE IF EXISTS geni_dates;
DROP TABLE IF EXISTS geni_names;
DROP TABLE IF EXISTS geni_phone_numbers;
DROP TABLE IF EXISTS geni_photo_sizes;
DROP TABLE IF EXISTS geni_video_sizes;
DROP TABLE IF EXISTS geni_document_sizes;
DROP TABLE IF EXISTS geni_paths;
DROP TABLE IF EXISTS geni_relations;
DROP TABLE IF EXISTS geni_runs;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
