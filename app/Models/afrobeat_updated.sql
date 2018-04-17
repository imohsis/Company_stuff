-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2016 at 07:39 AM
-- Server version: 10.1.10-MariaDB
-- PHP Version: 7.0.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `afrobeat`
--

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_albums`
--

CREATE TABLE `afrobt_albums` (
  `id` int(11) NOT NULL,
  `artist_uid` varchar(13) NOT NULL,
  `album_name` varchar(255) NOT NULL,
  `album_cover` varchar(30) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_album_comments`
--

CREATE TABLE `afrobt_album_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `album_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_album_likes`
--

CREATE TABLE `afrobt_album_likes` (
  `id` bigint(13) UNSIGNED NOT NULL,
  `album_id` int(11) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_album_rate`
--

CREATE TABLE `afrobt_album_rate` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `album_id` int(11) NOT NULL,
  `value` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_artists`
--

CREATE TABLE `afrobt_artists` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `artist_name` varchar(60) NOT NULL,
  `avatar` varchar(30) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_charts`
--

CREATE TABLE `afrobt_charts` (
  `id` int(32) UNSIGNED NOT NULL,
  `uid` varchar(13) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_chart_comments`
--

CREATE TABLE `afrobt_chart_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `chart_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_chart_likes`
--

CREATE TABLE `afrobt_chart_likes` (
  `id` bigint(13) UNSIGNED NOT NULL,
  `chart_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_convs`
--

CREATE TABLE `afrobt_convs` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `partner_id` varchar(13) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_convs_details`
--

CREATE TABLE `afrobt_convs_details` (
  `id` int(11) NOT NULL,
  `conv_uid` varchar(13) NOT NULL,
  `sender_id` varchar(13) NOT NULL,
  `message` text,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_genres`
--

CREATE TABLE `afrobt_genres` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `genre` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_movie_charts`
--

CREATE TABLE `afrobt_movie_charts` (
  `id` int(32) UNSIGNED NOT NULL,
  `chart_uid` varchar(13) NOT NULL,
  `movie_title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `rank` tinyint(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_music`
--

CREATE TABLE `afrobt_music` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `admin_uid` varchar(13) NOT NULL,
  `genre_id` int(11) UNSIGNED NOT NULL COMMENT 'Linked  id from table "afrobt_music_genres"',
  `artist_uid` varchar(13) NOT NULL,
  `album_id` int(11) UNSIGNED NOT NULL COMMENT 'Linked from "afrobt_music_album"',
  `filename` varchar(100) NOT NULL,
  `title` varchar(150) NOT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `duration` varchar(10) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_music_charts`
--

CREATE TABLE `afrobt_music_charts` (
  `id` int(32) UNSIGNED NOT NULL,
  `chart_uid` varchar(13) NOT NULL,
  `song_title` varchar(255) NOT NULL,
  `artist_name` varchar(100) NOT NULL,
  `rank` tinyint(6) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_music_comments`
--

CREATE TABLE `afrobt_music_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_music_likes`
--

CREATE TABLE `afrobt_music_likes` (
  `id` bigint(13) UNSIGNED NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_music_rate`
--

CREATE TABLE `afrobt_music_rate` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `value` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_news`
--

CREATE TABLE `afrobt_news` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `admin_id` varchar(13) NOT NULL,
  `news_title` tinytext,
  `cover_img` tinytext,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `cat_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `afrobt_news`
--

INSERT INTO `afrobt_news` (`id`, `uid`, `admin_id`, `news_title`, `cover_img`, `created_at`, `updated_at`, `status`, `cat_id`, `views`, `slug`, `cdn_status`) VALUES
(1, '179890181001', '489idfkhj3', 'Linkin Park Interview Now', 'cover_7565484.png', '2016-08-01 16:13:16', '2016-08-01 16:06:02', 1, 1, NULL, 'linkin-park-interview', 0),
(2, '907199788790', '489idfkhj3', 'Linkin Park Interview', 'cover_7565484.png', '2016-08-01 16:14:05', '2016-08-01 17:01:48', 1, 1, NULL, 'linkin-park-interview-1199', 0),
(3, '717019108818', '489idfkhj3', 'Linkin Park Interview', 'cover_7565484.png', '2016-08-04 15:30:56', '2016-08-04 14:30:56', 1, 1, NULL, 'linkin-park-interview-0181', 0);

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_news_catg`
--

CREATE TABLE `afrobt_news_catg` (
  `id` int(11) NOT NULL,
  `cat_name` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `afrobt_news_catg`
--

INSERT INTO `afrobt_news_catg` (`id`, `cat_name`, `status`) VALUES
(1, 'Tech News', 1),
(2, 'Traditional News', 1),
(3, 'Afro Rap News', 1),
(4, 'Afro News', 1),
(5, 'African Juju News', 1);

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_news_comments`
--

CREATE TABLE `afrobt_news_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `news_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_news_likes`
--

CREATE TABLE `afrobt_news_likes` (
  `id` int(13) UNSIGNED NOT NULL,
  `news_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_news_section`
--

CREATE TABLE `afrobt_news_section` (
  `id` int(11) NOT NULL,
  `news_uid` varchar(255) NOT NULL,
  `title` varchar(225) NOT NULL,
  `content` longtext NOT NULL,
  `images` longtext NOT NULL,
  `local_images` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_playlist`
--

CREATE TABLE `afrobt_playlist` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `title` tinytext,
  `type` tinyint(4) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_playlist_files`
--

CREATE TABLE `afrobt_playlist_files` (
  `id` int(11) NOT NULL,
  `playlist_uid` varchar(13) NOT NULL,
  `file_uid` varchar(13) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_users_follow`
--

CREATE TABLE `afrobt_users_follow` (
  `id` int(11) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `follower_id` varchar(13) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_user_music`
--

CREATE TABLE `afrobt_user_music` (
  `id` int(11) NOT NULL,
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
  `slug` varchar(255) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_user_music_comments`
--

CREATE TABLE `afrobt_user_music_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_user_music_likes`
--

CREATE TABLE `afrobt_user_music_likes` (
  `id` bigint(13) UNSIGNED NOT NULL,
  `music_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_user_videos`
--

CREATE TABLE `afrobt_user_videos` (
  `id` int(11) NOT NULL,
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
  `slug` varchar(255) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_user_videos_comments`
--

CREATE TABLE `afrobt_user_videos_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_user_videos_likes`
--

CREATE TABLE `afrobt_user_videos_likes` (
  `id` bigint(13) UNSIGNED NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_videos`
--

CREATE TABLE `afrobt_videos` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `admin_id` varchar(13) NOT NULL COMMENT 'Linked from "afrobt_users"',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Linked  id from table "afrobt_videos_genres"',
  `artist_uid` varchar(13) NOT NULL DEFAULT '0' COMMENT 'Linked ID from table "afrobt_videos_artists". ID of the artists this song belongs to.',
  `title` varchar(150) NOT NULL,
  `video_cover` varchar(150) DEFAULT NULL,
  `link` varchar(150) DEFAULT NULL,
  `views` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `slug` varchar(255) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_videos_comments`
--

CREATE TABLE `afrobt_videos_comments` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `comment_text` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_videos_likes`
--

CREATE TABLE `afrobt_videos_likes` (
  `id` bigint(13) UNSIGNED NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_videos_rate`
--

CREATE TABLE `afrobt_videos_rate` (
  `id` int(12) UNSIGNED NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `video_uid` varchar(13) NOT NULL,
  `value` int(2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `afrobt_video_types`
--

CREATE TABLE `afrobt_video_types` (
  `id` smallint(6) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uid` varchar(13) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  `acc_type` varchar(1) DEFAULT NULL,
  `acc_verification` tinyint(1) NOT NULL DEFAULT '0',
  `activation_code` varchar(42) NOT NULL,
  `cdn_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uid`, `full_name`, `email`, `username`, `password`, `avatar`, `created_at`, `updated_at`, `status`, `acc_type`, `acc_verification`, `activation_code`, `cdn_status`) VALUES
(1, '489idfkhj3', 'solomon nweze', 'solomon@yahoo.com', 'solomon', '$2y$10$En/0y8brgAKc5gULVA5AOOTjj/P7qt7iP8ASseoHaR/Xr1.i6u0/y', 'my_afrobeat.jpg', '2016-07-27 00:00:00', '0000-00-00 00:00:00', 1, '1', 1, '89f43ui@98&$%REHG', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `afrobt_albums`
--
ALTER TABLE `afrobt_albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `slug` (`slug`),
  ADD KEY `artist_uid` (`artist_uid`);

--
-- Indexes for table `afrobt_album_comments`
--
ALTER TABLE `afrobt_album_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_album_likes`
--
ALTER TABLE `afrobt_album_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_album_rate`
--
ALTER TABLE `afrobt_album_rate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_artists`
--
ALTER TABLE `afrobt_artists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `slug` (`slug`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `afrobt_charts`
--
ALTER TABLE `afrobt_charts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_chart_comments`
--
ALTER TABLE `afrobt_chart_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_chart_likes`
--
ALTER TABLE `afrobt_chart_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_convs`
--
ALTER TABLE `afrobt_convs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_convs_details`
--
ALTER TABLE `afrobt_convs_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_genres`
--
ALTER TABLE `afrobt_genres`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_movie_charts`
--
ALTER TABLE `afrobt_movie_charts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_music`
--
ALTER TABLE `afrobt_music`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `status` (`status`),
  ADD KEY `slug` (`slug`);

--
-- Indexes for table `afrobt_music_charts`
--
ALTER TABLE `afrobt_music_charts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_music_comments`
--
ALTER TABLE `afrobt_music_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_music_likes`
--
ALTER TABLE `afrobt_music_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_music_rate`
--
ALTER TABLE `afrobt_music_rate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_news`
--
ALTER TABLE `afrobt_news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `status` (`status`),
  ADD KEY `slug` (`slug`);

--
-- Indexes for table `afrobt_news_catg`
--
ALTER TABLE `afrobt_news_catg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_news_comments`
--
ALTER TABLE `afrobt_news_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_news_likes`
--
ALTER TABLE `afrobt_news_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_news_section`
--
ALTER TABLE `afrobt_news_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_uid` (`news_uid`);

--
-- Indexes for table `afrobt_playlist`
--
ALTER TABLE `afrobt_playlist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_users_follow`
--
ALTER TABLE `afrobt_users_follow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_user_music`
--
ALTER TABLE `afrobt_user_music`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `status` (`status`),
  ADD KEY `slug` (`slug`);

--
-- Indexes for table `afrobt_user_music_comments`
--
ALTER TABLE `afrobt_user_music_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_user_music_likes`
--
ALTER TABLE `afrobt_user_music_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_user_videos`
--
ALTER TABLE `afrobt_user_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slug` (`slug`),
  ADD KEY `uid` (`uid`),
  ADD KEY `slug_2` (`slug`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `afrobt_user_videos_comments`
--
ALTER TABLE `afrobt_user_videos_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_user_videos_likes`
--
ALTER TABLE `afrobt_user_videos_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_videos`
--
ALTER TABLE `afrobt_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `afrobt_videos_comments`
--
ALTER TABLE `afrobt_videos_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_videos_likes`
--
ALTER TABLE `afrobt_videos_likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_videos_rate`
--
ALTER TABLE `afrobt_videos_rate`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `afrobt_video_types`
--
ALTER TABLE `afrobt_video_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_2` (`email`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD KEY `uid` (`uid`),
  ADD KEY `email` (`email`),
  ADD KEY `username` (`username`),
  ADD KEY `password` (`password`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `afrobt_albums`
--
ALTER TABLE `afrobt_albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_album_comments`
--
ALTER TABLE `afrobt_album_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_album_likes`
--
ALTER TABLE `afrobt_album_likes`
  MODIFY `id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_album_rate`
--
ALTER TABLE `afrobt_album_rate`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_artists`
--
ALTER TABLE `afrobt_artists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_charts`
--
ALTER TABLE `afrobt_charts`
  MODIFY `id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_chart_comments`
--
ALTER TABLE `afrobt_chart_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_chart_likes`
--
ALTER TABLE `afrobt_chart_likes`
  MODIFY `id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_convs`
--
ALTER TABLE `afrobt_convs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_convs_details`
--
ALTER TABLE `afrobt_convs_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_genres`
--
ALTER TABLE `afrobt_genres`
  MODIFY `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_movie_charts`
--
ALTER TABLE `afrobt_movie_charts`
  MODIFY `id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_music`
--
ALTER TABLE `afrobt_music`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_music_charts`
--
ALTER TABLE `afrobt_music_charts`
  MODIFY `id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_music_comments`
--
ALTER TABLE `afrobt_music_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_music_likes`
--
ALTER TABLE `afrobt_music_likes`
  MODIFY `id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_music_rate`
--
ALTER TABLE `afrobt_music_rate`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_news`
--
ALTER TABLE `afrobt_news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `afrobt_news_catg`
--
ALTER TABLE `afrobt_news_catg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `afrobt_news_comments`
--
ALTER TABLE `afrobt_news_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_news_likes`
--
ALTER TABLE `afrobt_news_likes`
  MODIFY `id` int(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_news_section`
--
ALTER TABLE `afrobt_news_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_playlist`
--
ALTER TABLE `afrobt_playlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_users_follow`
--
ALTER TABLE `afrobt_users_follow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_user_music`
--
ALTER TABLE `afrobt_user_music`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_user_music_comments`
--
ALTER TABLE `afrobt_user_music_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_user_music_likes`
--
ALTER TABLE `afrobt_user_music_likes`
  MODIFY `id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_user_videos`
--
ALTER TABLE `afrobt_user_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_user_videos_comments`
--
ALTER TABLE `afrobt_user_videos_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_user_videos_likes`
--
ALTER TABLE `afrobt_user_videos_likes`
  MODIFY `id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_videos`
--
ALTER TABLE `afrobt_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_videos_comments`
--
ALTER TABLE `afrobt_videos_comments`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_videos_likes`
--
ALTER TABLE `afrobt_videos_likes`
  MODIFY `id` bigint(13) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_videos_rate`
--
ALTER TABLE `afrobt_videos_rate`
  MODIFY `id` int(12) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `afrobt_video_types`
--
ALTER TABLE `afrobt_video_types`
  MODIFY `id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
