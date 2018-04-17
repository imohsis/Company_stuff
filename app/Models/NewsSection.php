<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;
class NewsSection extends Model
{
    //
    protected $table = 'afrobt_news_section';

    protected $guarded = [];
    public $timestamps = false;


    public  function createNewsSection( $newsUid, $title,$content,$images,$localImages,$link,$embed)
    {

        $data = array(
            'news_uid' => $newsUid,
            'title' => $title,
            'content' => $content,
            'link' => $link,
            'embed' => $embed,
            'images' => $images,
            'local_images' => $localImages


        );
        return self::create($data);
    }

    public  function updateNewsSection($sectionId,$newsUid, $title,$content,$link,$embed)
    {

        $data = array(
            'title' => $title,
            'content' => $content,
            'embed' => $embed,
            'link' => $link
        );
        return self::where('id',$sectionId)->where("news_uid" , $newsUid)
            ->update($data);
    }

    public  function updateImage( $sectionId,$newsUid,$images,$localImages)
    {

        $data = array(
            'images' => $images,
            'local_images' => $localImages

        );
        return self::where('id',$sectionId)->where("news_uid" , $newsUid)
            ->update($data);
    }

    public function getSection($section_id,$news_uid){
        return self::where("id" , $section_id)->where('news_uid',$news_uid)->first();
    }


    public function countSection($news_uid){
        return self::where('news_uid',$news_uid)->get();
    }

    public function getSections($section_id,$news_uid){
        return self::where("id" , $section_id)->where('news_uid',$news_uid)->get();
    }

    public function hasPushedToCDN($uid)
    {
        return Utility::updateCDNStatus($this->table, $uid, Utility::CDN_STATUS_YES);
    }

}
