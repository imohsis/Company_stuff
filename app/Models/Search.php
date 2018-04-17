<?php

namespace App\Models;

use App\Helpers\Utility;
use DB;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
	//protected $table = 'afrobt_artists';

    //protected $guarded = [];


    public function getSearch($keyword, $start = 0, $limit = 5) {
        $query = DB::select(
            "SELECT
                `id` AS `uid`,
                `album_cover` AS `picture`,
                `album_name` AS `title`, 
                `slug`,
                '' AS `link`,
                '' AS `username`,
                '' AS `full_name`,
                '' AS `avatar`,
                '' AS `email`,
                '' AS `artist_uid`,
                `cdn_status`,
                '' AS `user_cdn_status`,
                'albums' AS `record_type` 
            FROM 
                `afrobt_albums`
            WHERE
                `album_name` LIKE '%$keyword%' OR `slug` LIKE '%$keyword%'
            UNION ALL(
                SELECT 
                    `a`.`id` AS `uid`,
                    `a`.`avatar` AS `picture`,
                    `a`.`full_name` AS `title`, 
                    `a`.`slug`,
                    '' AS `link`,
                    '' AS `username`,
                    '' AS `full_name`,
                    '' AS `avatar`,
                    '' AS `email`,
                    '' AS `artist_uid`,
                    `a`.`cdn_status`,
                     '' AS `user_cdn_status`,
                    'artist' AS `record_type` 
                FROM 
                    `users` `a`
                WHERE
                    `full_name` LIKE '%$keyword%' OR `slug` LIKE '%$keyword%'
            ) UNION ALL (
                SELECT 
                    `al`.`id` AS `uid`,
                    `al`.`album_cover` AS `picture`,
                    `m`.`title`, 
                    `m`.`slug`,
                    '' AS `link`,
                    '' AS `username`,
                    '' AS `full_name`,
                    '' AS `avatar`,
                    '' AS `email`,
                    '' AS `artist_uid`,
                    `m`.`cdn_status`, 
                    '' AS `user_cdn_status`,
                    'music' AS `record_type` 
                FROM 
                    `afrobt_genres` `g`
                INNER JOIN
                    `afrobt_music` `m` ON `m`.`genre_id` = `g`.`id`
                INNER JOIN
                    `afrobt_albums` `al` ON `m`.`album_id` = `al`.`id`
                WHERE
                    `g`.`genre` LIKE '%$keyword%' OR `m`.`title` LIKE '%$keyword%' OR `m`.`slug` LIKE '%$keyword%'
            ) UNION ALL(
                SELECT
                    `uid` AS `uid`,
                    `thumbnail` AS `picture`,
                    `content` AS `title`, 
                    `uid` AS `slug`,
                    '' AS `link`,
                    '' AS `username`,
                    '' AS `full_name`,
                    '' AS `avatar`,
                    '' AS `email`,
                    '' AS `artist_uid`,
                    `thumbnail_cdn_status` AS `cdn_status`,
                    '' AS `user_cdn_status`,
                    'live_feed' AS `record_type` 
                FROM 
                    `afrobt_live_feeds`
                WHERE
                    `content` LIKE '%$keyword%' OR `feed_type` LIKE '%$keyword%'
            ) UNION ALL(
                SELECT
                    `n`.`uid` AS `uid`,
                    `n`.`cover_img` AS `picture`,
                    `n`.`news_title` AS `title`, 
                    `n`.`slug`, 
                    '' AS `link`,
                    '' AS `username`,
                    '' AS `full_name`,
                    '' AS `avatar`,
                    '' AS `email`,
                    '' AS `artist_uid`,
                    `n`.`cdn_status`,
                    '' AS `user_cdn_status`,
                    'news' AS `record_type` 
                FROM 
                    `afrobt_news` `n` 
                INNER JOIN 
                    `afrobt_news_section` `ns` ON `ns`.`news_uid` = `n`.`uid` 
                WHERE
                    `n`.`news_title` LIKE '%$keyword%' OR `n`.`slug` LIKE '%$keyword%' OR 
                    `ns`.`title` LIKE '%$keyword%' OR `ns`.`content` LIKE '%$keyword%'
            ) UNION ALL(
                SELECT
                    `v`.`uid` AS `uid`,
                    `v`.`video_cover` AS `picture`,
                    `v`.`title`, 
                    `v`.`slug`, 
                    `v`.`link`,
                    `us`.`username`,
                    `us`.`full_name`,
                    `us`.`avatar`,
                    `us`.`email`,
                    `v`.`artist_uid`,
                    `v`.`cdn_status`,
                    `us`.`cdn_status` AS `user_cdn_status`,
                    'video' AS `record_type` 
                FROM 
                    `afrobt_videos` `v`
                INNER JOIN 
                    `users` `us` ON `v`.`artist_uid` = `us`.`uid`
                WHERE
                    `v`.`title` LIKE '%$keyword%' OR `v`.`slug` LIKE '%$keyword%'
            ) LIMIT {$start}, {$limit}"
        );

        if(!empty($query))
        {
            foreach ($query as $key => $result) 
            {
                if($result->record_type == 'albums')
                {

                    //Customize albums
                    $data[] = array(

                        "uid"       => $result->uid,
                        "title"       => $result->title,
                        "slug"        => $result->slug,
                        "record_type" => $result->record_type,
                        "photo_url"  =>  $this->photo_url($result->cdn_status,$result->picture)
                    );
                }
                else if ($result->record_type == 'artist')
                {
                    //Customize Artist
                    $data[] = array(

                        "uid"       => $result->uid,
                        "title"       => $result->title,
                        "slug"        => $result->slug,
                        "record_type" => $result->record_type,
                        "photo_url"  =>  $this->photo_url($result->cdn_status,$result->picture)
                    );
                }
                else if ($result->record_type == 'music')
                {
                    //Customize Music
                    $data[] = array(

                        "uid"       => $result->uid,
                        "title"       => $result->title,
                        "slug"        => $result->slug,
                        "record_type" => $result->record_type,
                        "photo_url"  =>  $this->photo_url($result->cdn_status,$result->picture),
                        "song_url"  =>  $this->song_url($result->cdn_status,$result->slug)
                    );
                }
                else if ($result->record_type == 'live_feed')
                {
                    //Customize Live feeds
                    $data[] = array(

                        "uid"       => $result->uid,
                        "title"       => $result->title,
                        "slug"        => $result->slug,
                        "record_type" => $result->record_type,
                        "photo_url"  =>  $this->photo_url($result->cdn_status,$result->picture)
                    );
                }
                else if ($result->record_type == 'news')
                {
                    //Customize News
                    $data[] = array(

                        "uid"       => $result->uid,
                        "title"       => $result->title,
                        "slug"        => $result->slug,
                        "record_type" => $result->record_type,
                        "photo_url"  =>  $this->photo_url($result->cdn_status,$result->picture)
                    );
                }
                else if ($result->record_type == 'video')
                {
                    //Customize Videos
                    $videoArray = array(

                        "uid"       => $result->uid,
                        "title"       => $result->title,
                        "slug"        => $result->slug,
                        "record_type" => $result->record_type,
                        "link" => $result->link,
                        "photo_url"  =>  $this->photo_url($result->cdn_status,$result->picture)
                    );
                    $artist = new \stdClass();
                    $artist->uid = $result->artist_uid;
                    $artist->username = $result->username;
                    $artist->full_name = $result->full_name;
                    $artist->email = $result->email;
                    $artist->avatar = $this->photo_url($result->user_cdn_status,$result->avatar);

                    $videoArray['artist'] = $artist;
                    $data[] = $videoArray;
                }
            }
            return $data;
        }
        else
        {
            return '';
        }
    }

    public function photo_url($cdn_status,$photo_name){
            $photo_url = '';
        if($cdn_status == Utility::CDN_STATUS_YES){
            $photo_url = Utility::CDN_IMAGE_URL . $photo_name;
        }
        else{
            $photo_url = Utility::TEMP_IMAGE_URL . $photo_name;
        }
        return $photo_url;
    }

    public function song_url($cdn_status,$song_name){
        $song_url = '';
        if($cdn_status == Utility::CDN_STATUS_YES){
            $song_url = Utility::CDN_AUDIO_URL . $song_name;
        }
        else{
            $song_url = Utility::TEMP_AUDIO_URL . $song_name;
        }
        return $song_url;
    }

}
