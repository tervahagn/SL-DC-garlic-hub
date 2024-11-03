CREATE TABLE `user_main` (
    `UID` INTEGER PRIMARY KEY,
    `company_id` INTEGER NOT NULL DEFAULT 1,
    `last_access` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `login_time` TIMESTAMP DEFAULT NULL,
    `num_logins` INTEGER NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT NULL,
    `status` INTEGER NOT NULL DEFAULT 0,
    `locale` CHAR(5) DEFAULT NULL,
    `session_id` CHAR(32) DEFAULT '',
    `username` CHAR(50) DEFAULT '',
    `password` CHAR(60) DEFAULT NULL,
    `gender` VARCHAR(10) DEFAULT NULL,
    `email` VARCHAR(100) NOT NULL DEFAULT '',
    `last_password_change` TIMESTAMP DEFAULT NULL,
    UNIQUE (`email`)
);

INSERT INTO `user_main`
(`company_id`, `last_access`, `login_time`, `num_logins`, `created_at`, `status`, `locale`, `session_id`, `username`, `password`, `gender`, `email`, `last_password_change`)
VALUES
    (1, CURRENT_TIMESTAMP, NULL, 1, CURRENT_TIMESTAMP, 1, 'en_US', '', 'admin', '$2y$10$GNIvEOnYy5OxEfdnMO0O0O2g1myLht2CTK4SaVfMK664O85Sd4MA6', '', 'example@example.com', NULL);

CREATE TABLE mediapool_nodes (
    node_id INTEGER PRIMARY KEY AUTOINCREMENT,
    root_id INTEGER NOT NULL DEFAULT 0,
    parent_id INTEGER NOT NULL DEFAULT 0,
    level INTEGER NOT NULL,
    root_order INTEGER NOT NULL,
    lft INTEGER NOT NULL DEFAULT 0,
    rgt INTEGER NOT NULL DEFAULT 0,
    UID INTEGER NOT NULL DEFAULT 0,
    domain_ids INTEGER NOT NULL,
    is_public INTEGER NOT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    create_date TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:00',
    `name` TEXT NOT NULL DEFAULT '',
    storage_type TEXT NOT NULL CHECK(storage_type IN ('db', 'dropbox', 'azure', 'google', 'webdav', 's3', 'ftp')) DEFAULT db,
    credentials TEXT
);
CREATE INDEX idx_mediapool_nodes_root_id ON mediapool_nodes (root_id);
-- set some default root dirs.
INSERT INTO mediapool_nodes (root_id, parent_id, level, root_order, lft, rgt, UID, domain_ids, is_public, last_updated, create_date, name)
VALUES (1, 0, 1, 1, 1, 8, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'public');

INSERT INTO mediapool_nodes (root_id, parent_id, level, root_order, lft, rgt, UID, domain_ids, is_public, last_updated, create_date, name)
VALUES (2, 0, 2, 1, 1, 4, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'user');

-- set some default dirs under public
INSERT INTO mediapool_nodes (root_id, parent_id, level, root_order, lft, rgt, UID, domain_ids, is_public, last_updated, create_date, name)
VALUES (1, 1, 2, 1, 2, 3, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'images');
INSERT INTO mediapool_nodes (root_id, parent_id, level, root_order, lft, rgt, UID, domain_ids, is_public, last_updated, create_date, name)
VALUES (1, 1, 2, 1, 4, 5, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'videos');
INSERT INTO mediapool_nodes (root_id, parent_id, level, root_order, lft, rgt, UID, domain_ids, is_public, last_updated, create_date, name)
VALUES (1, 1, 2, 1, 6, 7, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'widgets');

-- set admin dir under user
INSERT INTO mediapool_nodes (root_id, parent_id, level, root_order, lft, rgt, UID, domain_ids, is_public, last_updated, create_date, name)
VALUES (2, 0, 2, 1, 2, 3, 1, 0, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'admin');


CREATE TABLE mediapool_media (
     media_id INTEGER PRIMARY KEY AUTOINCREMENT,
     node_id INTEGER NOT NULL DEFAULT 0,
     deleted INTEGER NOT NULL DEFAULT 0,
     preview INTEGER NOT NULL DEFAULT 0,
     last_UID INTEGER NOT NULL DEFAULT 0,
     last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     UID INTEGER NOT NULL DEFAULT 0,
     upload_time TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:00',
     filetype TEXT NOT NULL DEFAULT '',
     filename TEXT NOT NULL DEFAULT '',
     filesize INTEGER NOT NULL DEFAULT 0,
     duration INTEGER NOT NULL DEFAULT 0,
     mediatype TEXT NOT NULL,
     tags TEXT DEFAULT NULL,
     media_description TEXT DEFAULT NULL
);

CREATE INDEX idx_mediapool_node_id ON mediapool_media (node_id);
CREATE INDEX idx_mediapool_mediatype ON mediapool_media (mediatype);
CREATE INDEX idx_mediapool_deleted ON mediapool_media (deleted);
