CREATE TABLE `afrobt_albums` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `artist_uid` varchar(13) NOT NULL,
  `album_name` varchar(255) NOT NULL,
  `album_cover` varchar(30) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
);
CREATE TABLE `afrobt_artists` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `artist_name` varchar(60) NOT NULL,
  `avatar` varchar(30) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
);

CREATE TABLE `afrobt_convs` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `partner_id` varchar(13) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL
);

CREATE TABLE `afrobt_convs_details` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `conv_uid` varchar(13) NOT NULL,
  `sender_id` varchar(13) NOT NULL,
  `message` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL
);

CREATE TABLE `afrobt_genres` (
  `id` smallint(6) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `genre` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_music` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `admin_uid` varchar(13) NOT NULL,
  `genre_id` int(11) UNSIGNED NOT NULL COMMENT 'Linked  id from table "afrobt_music_genres"',
  `artist_uid` varchar(13) NOT NULL,
  `album_id` int(11) UNSIGNED NOT NULL COMMENT 'Linked from "afrobt_music_album"',
  `filename` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `duration` smallint(6) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_music_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_music_likes` (
  `id` bigint(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `music_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_news` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `admin_id` varchar(13) NOT NULL,
  `news_title` tinytext,
  `content` longblob NOT NULL,
  `cover_img` tinytext,
  `created_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `cat_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_news_catg` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `cat_name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_news_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `news_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_news_likes` (
  `id` int(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `news_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_playlist` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `title` tinytext,
  `type` tinyint(4) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_playlist_files` (
  `id` int(11) NOT NULL,
  `playlist_uid` varchar(13) NOT NULL,
  `file_uid` varchar(13) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `users` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `acc_type` varchar(1) DEFAULT NULL,
  `acc_verification` tinyint(1) NOT NULL DEFAULT '0',
  `activation_code` varchar(42) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_users_follow` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `follower_id` varchar(13) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_videos` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `admin_id` varchar(13) NOT NULL COMMENT 'Linked from "afrobt_users"',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Linked  id from table "afrobt_videos_genres"',
  `artist_uid` varchar(13) NOT NULL DEFAULT '0' COMMENT 'Linked ID from table "afrobt_videos_artists". ID of the artists this song belongs to.',
  `title` varchar(150) NOT NULL,
  `video_cover` varchar(150) DEFAULT NULL,
  `filename` varchar(150) DEFAULT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `slug` varchar(255) NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_videos_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_videos_likes` (
  `id` bigint(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `video_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_user_music` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `genre_id` int(11) UNSIGNED NOT NULL COMMENT 'Linked  id from table "afrobt_music_genres"',
  `artist_name` varchar(70) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` varchar(255) NOT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `duration` smallint(6) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_user_music_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_user_music_likes` (
  `id` bigint(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `music_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_user_videos` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL COMMENT 'Linked from "afrobt_users"',
  `title` varchar(150) NOT NULL,
  `description` varchar(255) NOT NULL,
  `video_cover` varchar(150) DEFAULT NULL,
  `filename` varchar(150) DEFAULT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `duration` smallint(6) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_user_videos_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_user_videos_likes` (
  `id` bigint(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `video_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_video_types` (
  `id` smallint(6) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_charts` (
  `id` int(32) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uid` varchar(13) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_music_charts` (
  `id` int(32) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `chart_uid` varchar(13) NOT NULL,
  `song_title` varchar(255) NOT NULL,
  `artist_name` varchar(100) NOT NULL,
  `rank` tinyint(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_movie_charts` (
  `id` int(32) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `chart_uid` varchar(13) NOT NULL,
  `movie_title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `rank` tinyint(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_chart_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `chart_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_chart_likes` (
  `id` bigint(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `chart_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_videos_rate` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `value` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_music_rate` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `value` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_album_comments` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `album_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_album_likes` (
  `id` bigint(13) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `afrobt_album_rate` (
  `id` int(12) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` varchar(13) NOT NULL,
  `album_id` int(11) NOT NULL,
  `value` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

