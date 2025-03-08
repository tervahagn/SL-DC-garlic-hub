INSERT INTO "user_main" ("UID","company_id","last_access","login_time","num_logins","created_at","status","locale","username","password","session_id","gender","email","last_password_change")
VALUES
(2,1,'2025-03-08 07:33:11',NULL,0,'2025-03-08 07:33:11',3,'en_US','horst','$2y$10$GNIvEOnYy5OxEfdnMO0O0O2g1myLht2CTK4SaVfMK664O85Sd4MA6',NULL,'','horst@example.com',NULL),
(3,1,'2025-03-08 07:33:11',NULL,0,'2025-03-08 07:33:11',3,'en_US','Günter','$2y$10$GNIvEOnYy5OxEfdnMO0O0O2g1myLht2CTK4SaVfMK664O85Sd4MA6',NULL,'','guenter@example.com',NULL),
(4,1,'2025-03-08 07:33:11',NULL,0,'2025-03-08 07:33:11',3,'en_US','zapappallas','$2y$10$GNIvEOnYy5OxEfdnMO0O0O2g1myLht2CTK4SaVfMK664O85Sd4MA6',NULL,'','zapappallas@example.com',NULL),
(4,1,'2025-03-08 07:33:11',NULL,0,'2025-03-08 07:33:11',3,'de_DE','reseller','$2y$10$GNIvEOnYy5OxEfdnMO0O0O2g1myLht2CTK4SaVfMK664O85Sd4MA6',NULL,'','zapappallas@example.com',NULL)
;

INSERT INTO "playlists" ("UID","time_limit","owner_duration","duration","filesize","shuffle","shuffle_picking","last_update","playlist_mode","playlist_name","external_playlist_link","multizone")
VALUES
(1,0,0,0,0,0,0,'2025-02-22 09:56:44','master','hurzi',NULL,NULL),
(1,0,0,0,0,0,0,'2025-02-26 10:05:58','internal','interne Playliste',NULL,NULL),
(1,0,0,0,0,0,0,'2025-02-26 10:07:03','external','externe Playliste',NULL,NULL),
(1,0,0,0,0,0,0,'2025-02-26 10:20:56','channel','Das ist ein Kanal und so',NULL,NULL),
(1,0,0,0,0,0,0,'2025-03-07 14:24:21','multizone','Multizone',NULL,NULL),
(2,0,0,0,0,0,0,'2025-02-22 09:56:44','master','hurzi 2',NULL,NULL),
(2,0,0,0,0,0,0,'2025-02-26 10:05:58','internal','interne Playliste 2',NULL,NULL),
(2,0,0,0,0,0,0,'2025-02-26 10:07:03','external','externe Playliste 2',NULL,NULL),
(2,0,0,0,0,0,0,'2025-02-26 10:20:56','channel','Das ist ein Kanal 2',NULL,NULL),
(2,0,0,0,0,0,0,'2025-03-07 14:24:21','multizone','Multizone 2',NULL,NULL),
(3,0,0,0,0,0,0,'2025-02-22 09:56:44','master','hurzi 3',NULL,NULL),
(3,0,0,0,0,0,0,'2025-02-26 10:05:58','internal','interne Playliste 3',NULL,NULL),
(3,0,0,0,0,0,0,'2025-02-26 10:07:03','external','externe Playliste 3',NULL,NULL),
(3,0,0,0,0,0,0,'2025-02-26 10:20:56','channel','Das ist ein Kanal 3',NULL,NULL),
(3,0,0,0,0,0,0,'2025-03-07 14:24:21','multizone','Multizone 3',NULL,NULL),
(4,0,0,0,0,0,0,'2025-02-22 09:56:44','master','hurzi 4',NULL,NULL),
(4,0,0,0,0,0,0,'2025-02-26 10:05:58','internal','interne Playliste 4',NULL,NULL),
(4,0,0,0,0,0,0,'2025-02-26 10:07:03','external','externe Playliste 4',NULL,NULL),
(4,0,0,0,0,0,0,'2025-02-26 10:20:56','channel','Das ist ein Kanal 4',NULL,NULL),
(4,0,0,0,0,0,0,'2025-03-07 14:24:21','multizone','Multizone 4',NULL,NULL)
;