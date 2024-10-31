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


