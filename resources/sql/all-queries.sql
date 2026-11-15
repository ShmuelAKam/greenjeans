
DB_CleanupManager.php:

21: $sql  = "DELETE FROM geni_runs WHERE run_id = :runId"; 

35: $sql = "SELECT run_id FROM geni_runs ORDER BY created_at DESC 
                LIMIT 18446744073709551615 OFFSET :keep";

49: $sql          = "DELETE FROM geni_runs WHERE run_id IN ($placeholders)"; 

61: $sql = "DELETE FROM geni_runs WHERE created_at < DATE_SUB(NOW(), INTERVAL $interval)"; 

---------------------------------------------------------------------

DB_Persistor.php:

61:        $sql = match ($driver) {
            'sqlite' => "SELECT name FROM sqlite_master
                     WHERE type = 'table' AND name NOT LIKE 'sqlite_%'",

            'mysql'  => "SELECT table_name AS name FROM information_schema.tables
                     WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'",

74: $sql  = "DELETE FROM `{$table}` WHERE run_id = :rid"; 


---------------------------------------------------------------------

DB_RunMapper.php:

37: $sql  = "INSERT INTO geni_runs (client_id, start_time) VALUES (?, ?)"; 

81: $sql = "UPDATE geni_runs 
                SET duration_s = ?, memory_alloc_mb = ?, run_fail = ?, run_complete = TRUE
                WHERE run_id = ?";

101: $sql = "SELECT g.*, c.id as profile_id 
                FROM geni_runs g
                JOIN geni_clients c ON g.client_id = c.client_id
                WHERE g.run_id = ?";


---------------------------------------------------------------------

DB_BaseMapper.php:

44: $sql = "SELECT 1 FROM `$tableName` WHERE `$idColumn` = :val AND run_id = :run_id LIMIT 1"; 

48: $sql = "DELETE FROM `$tableName` WHERE `$idColumn` = :val AND run_id = :run_id"; 

52: $sql = "SELECT * FROM `$tableName` WHERE `$idColumn` = :val AND run_id = :run_id LIMIT 1"; 

83: $sql  = "DELETE FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id"; 

95: $sql  = "SELECT COUNT(*) FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id"; 

117: $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val AND run_id = :run_id"; 

168: $sql = "SELECT * FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id"; 

207: $sql = "SELECT `{$targetCol}` FROM `{$this->tableName}` WHERE `{$column}` = :val AND run_id = :run_id ORDER BY `{$sortCol}` {$dir}"; 

304: $sql  = "SELECT * FROM `$tableName` WHERE `$idColumn` = :id"; 

310: $sql  = "SELECT * FROM `$tableName`"; 


---------------------------------------------------------------------

DB_LinkTableMapper.php:

60: $sql = match ($type) 
                'insert' => "INSERT INTO `$table` (`$c1`, `$c2`, run_id) VALUES (:key, :val, :run_id)",
                'select' => "SELECT `$c2` FROM `$table` WHERE `$c1` = :key AND run_id = :run_id {$orderClause}",
                'row'    => "SELECT * FROM `$table` WHERE `$c1` = :key AND run_id = :run_id {$orderClause}",
                'clear'  => "DELETE FROM `$table` WHERE `$c1` = :key AND run_id = :run_id",
                'count'  => "SELECT COUNT(*) FROM `$table` WHERE `$c1` = :key AND run_id = :run_id",

146: $sql  = "INSERT INTO `$table` (`$keyCol`, `$column`, `$seqCol`, run_id) VALUES (:key, :val, :seq, :run_id)"; 


---------------------------------------------------------------------

DB_CommentMapper.php:

28: $sql = "INSERT INTO geni_comments (owner_id, comment, created_at, title, run_id) 
                    VALUES (:owner, :comment, :created_at, :title, :run_id)";


---------------------------------------------------------------------

DB_DateMapper.php:

28: $sql = "INSERT INTO geni_dates ( 
                    owner_id,
                    circa, day, month, year,
                    end_circa, end_day, end_month, end_year,
                    date_range, run_id
                ) VALUES (
                    :owner_id,
                    :circa, :day, :month, :year,
                    :end_circa, :end_day, :end_month, :end_year,
                    :range, :run_id
                )";


---------------------------------------------------------------------

DB_DocumentImageSizeMapMapper.php:

30: $sql = "INSERT INTO geni_document_sizes ( 
                    url_large, url_origin, url_thumb, run_id)
                    VALUES (:large, :origin, :thumb, :run_id)";


---------------------------------------------------------------------

DB_EventMapper.php:

32: $sql = "INSERT INTO geni_events (owner_id, name, description, date_id, location_id, run_id) 
                VALUES (:owner_id, :name, :description, :date_id, :location_id, :run_id)";

38: $sql = "SELECT date_id, location_id FROM geni_events WHERE id = :id AND run_id = :run_id"; 


---------------------------------------------------------------------

DB_LocationMapper.php:

28: $sql = "INSERT INTO geni_locations ( 
                    owner_id,
                    country, county, city, place_name, state,
                    street_address1, street_address2, street_address3,
                    longitude, latitude, formatted_location, country_code,
                    run_id
                ) VALUES (
                    :owner_id,
                    :country, :county, :city, :place_name, :state,
                    :street_address1, :street_address2, :street_address3,
                    :longitude, :latitude, :formatted_location, :country_code,
                    :run_id
                )";


---------------------------------------------------------------------

DB_NameMapper.php:

39:        $sql = match ($driver)
        {
            'sqlite' => $baseSql . "
            ON CONFLICT (profile_id, locale, run_id) DO UPDATE SET
                display_name = EXCLUDED.display_name,
                first_name   = EXCLUDED.first_name,
                last_name    = EXCLUDED.last_name,
                maiden_name  = EXCLUDED.maiden_name,
                middle_name  = EXCLUDED.middle_name,
                suffix       = EXCLUDED.suffix,
                nicknames    = EXCLUDED.nicknames,
                title        = EXCLUDED.title",

            'mysql' => $baseSql . "
            AS new_vals ON DUPLICATE KEY UPDATE
                display_name = new_vals.display_name,
                first_name   = new_vals.first_name,
                last_name    = new_vals.last_name,
                maiden_name  = new_vals.maiden_name,
                middle_name  = new_vals.middle_name,
                suffix       = new_vals.suffix,
                nicknames    = new_vals.nicknames,
                title        = new_vals.title",


---------------------------------------------------------------------

DB_PhoneNumberMapper.php:

29: $sql = "INSERT INTO geni_phone_numbers (owner_id, area_code, country_code, number, type, run_id) 
                VALUES (:owner_id, :area_code, :country_code, :number, :type, :run_id)";


---------------------------------------------------------------------

DB_PhotoImageSizeMapMapper.php:

30: $sql = "INSERT INTO geni_photo_sizes ( 
                    url_large, url_medium, url_origin, url_print,
                    url_small, url_thumb, url_thumb2, run_id
                ) VALUES (
                    :large, :medium, :origin, :print,
                    :small, :thumb, :thumb2, :run_id
                )";


---------------------------------------------------------------------

DB_VideoImageSizeMapMapper.php:

29: $sql = "INSERT INTO geni_video_sizes ( 
                    url_large, url_medium, url_origin, url_print,
                    url_small, url_thumb, url_thumb2, run_id
                ) VALUES (
                    :large, :medium, :origin, :print,
                    :small, :thumb, :thumb2, :run_id
                )";


---------------------------------------------------------------------

DB_DocumentMapper.php:

51: $sql = "INSERT INTO geni_documents ( 
                    doc_id, title, description, text_content, attribution,
                    content_type, guid, page_count, source_url, url,
                    date_id, created_at, updated_at, location_id, size_id, run_id
                ) VALUES (
                    :doc_id, :title, :desc, :text, :attr,
                    :ctype, :guid, :pages, :src, :url,
                    :date_id, :created_at, :updated_at, :loc_id, :size_id, :run_id
                )";

66: $sql  = "SELECT date_id, location_id, size_id FROM geni_documents 
                    WHERE doc_id = :doc_id AND run_id = :run_id";

74: $sql = "UPDATE geni_documents SET 
                    title = :title, description = :desc, text_content = :text,
                    attribution = :attr, content_type = :ctype, guid = :guid,
                    page_count = :pages, source_url = :src, url = :url,
                    date_id = :date_id, created_at = :created_at,
                    updated_at = :updated_at, location_id = :loc_id, size_id = :size_id
                WHERE doc_id = :doc_id AND run_id = :run_id";


---------------------------------------------------------------------

DB_PhotoAlbumMapper.php:

31: $sql = "INSERT INTO geni_photo_albums ( 
                    album_id, name, description, photos_count, url,
                    cover_sizes_id, run_id
                ) VALUES (
                    :id, :name, :desc, :count, :url,
                    :cover_id, :run_id
                )";

42: $sql = "SELECT cover_sizes_id FROM geni_photo_albums WHERE album_id = :album_id AND run_id = :run_id"; 

47: $sql = "UPDATE geni_photo_albums SET 
                    name = :name, description = :desc, photos_count = :count,
                    url = :url, cover_sizes_id = :cover_id
                WHERE album_id = :id AND run_id = :run_id";


---------------------------------------------------------------------

DB_PhotoMapper.php:

44: $sql = "INSERT INTO geni_photos ( 
                    photo_id, album_id, title, description, attribution,
                    content_type, guid, url, created_at, updated_at,
                    date_id, location_id, size_id, run_id
                ) VALUES (
                    :id, :album, :title, :desc, :attr,
                    :ctype, :guid, :url, :created_at, :updated_at,
                    :date_id, :loc_id, :size_id, :run_id
                )";

63: $sql = "UPDATE geni_photos SET 
                    album_id = :album, title = :title, description = :desc,
                    attribution = :attr, content_type = :ctype, guid = :guid,
                    url = :url, created_at = :created_at, updated_at = :updated_at,
                    date_id = :date_id, location_id = :loc_id, size_id = :size_id
                WHERE photo_id = :id AND run_id = :run_id";


---------------------------------------------------------------------

DB_ProfileMapper.php:

47: $sql = "INSERT INTO geni_profiles ( 
            profile_id, guid, name, display_name, first_name, middle_name, last_name, maiden_name, suffix,
            gender, is_alive, public, claimed, big_tree, locked, master_profile, merge_pending,
            occupation, email, get_email, language, about_me, cause_of_death, merge_note,
            birth_id, death_id, burial_id, baptism_id, residence_id, location_id, mugshot_id,
            created_at, updated_at, documents_updated_at, photos_updated_at, videos_updated_at,
            creator_id, curator_id, merged_into_id, nicknames, run_id
        ) VALUES (
            :id, :guid, :name, :d_name, :f_name, :m_name, :l_name, :maiden, :suffix,
            :gender, :is_alive, :public, :claimed, :big_tree, :locked, :master, :merge_p,
            :occupation, :email, :get_email, :lang, :about, :cause, :merge_n,
            :birth_id, :death_id, :burial_id, :baptism_id, :res_id, :location_id, :mug_id,
            :created, :updated, :doc_up, :photo_up, :video_up,
            :creator, :curator, :merged, :nicknames, :run_id
        )";

66: $sql = "SELECT birth_id, death_id, burial_id, baptism_id, residence_id, mugshot_id, location_id 
                FROM geni_profiles WHERE profile_id = :profile_id AND run_id = :run_id";

72: $sql = "INSERT INTO geni_profile_details (profile_id, locale, detail_strings, run_id) 
                VALUES (:profile_id, :locale, :detail_strings, :run_id)";


---------------------------------------------------------------------

DB_ProjectMapper.php:

30: $sql = "INSERT INTO geni_projects ( 
                    project_id, name, description, created_at,
                    updated_at, url, run_id
                ) VALUES (
                    :id, :name, :desc, :created,
                    :updated, :url, :run_id
                )";

41: $sql = "UPDATE geni_projects SET 
                    name = :name, description = :desc,
                    created_at = :created, updated_at = :updated,
                    url = :url
                WHERE project_id = :id AND run_id = :run_id";


---------------------------------------------------------------------

DB_RelationMapper.php:

28: $sql = "INSERT INTO geni_relations (parent_id, name, relation_type, url, sequence_order, run_id) 
                VALUES (:pid, :name, :rel, :url, :seq, :run_id)";

95: $sql  = "DELETE FROM geni_relations WHERE parent_id = :pid AND run_id = :run_id"; 


---------------------------------------------------------------------

DB_RevisionMapper.php:

32: $sql = "INSERT INTO geni_revisions ( 
                    revision_id, guid, action, story,
                    date_local, time_local, server_timestamp, run_id
                ) VALUES (
                    :id, :guid, :action, :story,
                    :date_l, :time_l, :ts, :run_id
                )";

43: $sql = "UPDATE geni_revisions SET 
                    guid = :guid, action = :action, story = :story,
                    date_local = :date_l, time_local = :time_l,
                    server_timestamp = :ts
                WHERE revision_id = :id AND run_id = :run_id";


---------------------------------------------------------------------

DB_SurnameMapper.php:

31: $sql = "INSERT INTO geni_surnames ( 
                    surname_id, slugged_name, description, url,
                    follower_count, profile_count, run_id
                ) VALUES (
                    :id, :slug, :desc, :url,
                    :f_count, :p_count, :run_id
                )";

42: $sql = "UPDATE geni_surnames SET 
                    slugged_name = :slug, description = :desc,
                    url = :url, follower_count = :f_count,
                    profile_count = :p_count
                WHERE surname_id = :id AND run_id = :run_id";


---------------------------------------------------------------------

DB_UnionMapper.php:

36: $sql = "INSERT INTO geni_unions (union_id, status, marriage_id, divorce_id, run_id) 
                VALUES (:id, :status, :marriage_id, :divorce_id, :run_id)";

41: $sql = "SELECT marriage_id, divorce_id FROM geni_unions WHERE union_id = :union_id AND run_id = :run_id"; 

45: $sql = "UPDATE geni_unions SET 
                    status = :status, marriage_id = :marriage_id, divorce_id = :divorce_id
                WHERE union_id = :id AND run_id = :run_id";

51: $sql = "INSERT INTO geni_union_children (union_id, profile_id, child_type, run_id) VALUES (:union_id, :profile_id, :type, :run_id)"; 


---------------------------------------------------------------------

DB_UserMapper.php:

53: $sql = "INSERT INTO geni_users (name, account_type, metadata_json, run_id) 
                VALUES (:name, :account, :metadata, :run_id)";

60: $sql = "UPDATE geni_users SET 
                    name          = :name,
                    account_type  = :account,
                    metadata_json = :metadata
                WHERE run_id = :run_id";

70: $sql = "SELECT * FROM geni_users WHERE run_id = :run_id"; 

78: $sql          = "SELECT user_id FROM geni_users WHERE run_id = :run_id"; 

165: $sql = "UPDATE geni_users SET 
                        name         = :name,
                        account_type = :account
                    WHERE run_id = :run_id";

---------------------------------------------------------------------

DB_VideoMapper.php:

44: $sql = "INSERT INTO geni_videos ( 
                    video_id, title, description, attribution,
                    content_type, guid, url, created_at, updated_at,
                    date_id, location_id, size_id, run_id
                ) VALUES (
                    :id, :title, :desc, :attr,
                    :ctype, :guid, :url, :created_at, :updated_at,
                    :date_id, :loc_id, :size_id, :run_id
                )";

57: $sql = "SELECT date_id, location_id, size_id FROM geni_videos 
                    WHERE video_id = :video_id AND run_id = :run_id";

63: $sql = "UPDATE geni_videos SET 
                    title = :title, description = :desc, attribution = :attr,
                    content_type = :ctype, guid = :guid, url = :url,
                    created_at = :created_at, updated_at = :updated_at,
                    date_id = :date_id, location_id = :loc_id, size_id = :size_id
                WHERE video_id = :id AND run_id = :run_id";


---------------------------------------------------------------------

DB_DocumentMapperTest.php:

463: $sql  = "SELECT date_id, location_id, size_id FROM geni_documents WHERE doc_id = :id AND run_id = :rid"; 

527: $sql  = "INSERT INTO geni_documents (doc_id, run_id) VALUES (:id, :rid)"; 


---------------------------------------------------------------------

DB_MapperTestBase.php:

53: $sql = "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = :val AND run_id = :rid"; 

84: $sql  = "SELECT * FROM `$tableName` WHERE `$idColumn` = :id";

90: $sql  = "SELECT * FROM `$tableName`"; 


---------------------------------------------------------------------

DB_PhotoAlbumMapperTest.php:

209: $sql  = "SELECT cover_sizes_id FROM geni_photo_albums WHERE album_id = :id AND run_id = :rid"; 

254: $sql  = "INSERT INTO geni_photo_albums (album_id, run_id) VALUES (:id, :rid)"; 

---------------------------------------------------------------------

DB_PhotoMapperTest.php:

58: $sql  = "SELECT date_id, location_id, size_id FROM geni_photos WHERE photo_id = :photo_id AND run_id = :run_id"; 

362: $sql  = "SELECT date_id, location_id, size_id FROM geni_photos WHERE photo_id = :id AND run_id = :rid"; 

411: $sql  = "SELECT date_id, location_id, size_id FROM geni_photos WHERE photo_id = :id AND run_id = :rid"; 

450: $sql  = "INSERT INTO geni_photos (photo_id, run_id) VALUES (:id, :rid)"; 

---------------------------------------------------------------------

DB_ProjectMapperTest.php:

262: $sql  = "INSERT INTO geni_projects (project_id, run_id) VALUES (:id, :rid)"; 

---------------------------------------------------------------------

DB_SurnameMapperTest.php:

269: $sql  = "INSERT INTO geni_surnames (surname_id, run_id) VALUES (:id, :rid)"; 

---------------------------------------------------------------------

DB_VideoMapperTest.php:

316: $sql  = "SELECT date_id, location_id, size_id FROM geni_videos WHERE video_id = :id AND run_id = :rid"; 

365: $sql  = "SELECT date_id, location_id, size_id FROM geni_videos WHERE video_id = :id AND run_id = :rid"; 

403: $sql  = "INSERT INTO geni_videos (video_id, run_id) VALUES (:id, :rid)"; 

---------------------------------------------------------------------

dumpTable.php:

20: sql  = "SELECT * FROM `$tableName` WHERE `$idColumn` = :id";

26:         $sql  = "SELECT * FROM `$tableName`";

---------------------------------------------------------------------

GlobalSetup.php:

85: $sql = "INSERT INTO geni_clients (id, guid, name) VALUES ('test-id-1', 'test-guid-xyz', 'Test Client')"; 


