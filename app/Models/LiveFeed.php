<?php

namespace App\models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class LiveFeed extends Model
{
    //

    protected $table = 'afrobt_live_feeds';

    protected $guarded = [];

    public function poster_artist(){
        return $this->belongsTo('App\Models\Artist', 'poster_uid', 'uid');
    }

    public function live_feed_news(){
        return $this->belongsTo('App\Models\News', 'uid', 'uid');
    }

    public function poster_user(){
        return $this->belongsTo('App\User', 'poster_uid', 'uid');
    }

    public function getLiveFeed($status = "", $start = 0, $limit = 30){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , Utility::STATUS_ACTIVE)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getLiveFeedUpdates($lastID){

        return self::where('status' , Utility::STATUS_ACTIVE )
            ->where('id','>',$lastID)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getPosterLiveFeed($user_type,$start = 0, $limit = 20){

        return self::where("user_type" , $user_type)
            ->where("status",Utility::STATUS_ACTIVE )
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUniquePosterLiveFeed($posterUid,$start = 0, $limit = 20){

        return self::where("poster_uid" , $posterUid)
            ->where("status",Utility::STATUS_ACTIVE )
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSingleLiveFeed($uid){
        return self::where("uid" , $uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createLiveFeed($posterUid,$userType,$title,$thumbnail,$content,$feedType,
                                    $feedAttachment,$localImages)
    {
        $video = $feedAttachment;
        if($feedType == 'video'){
            $feedAttachment = '';
        }else{
            $video = '';
        }

        $uid = Utility::generateUID($this->table);

        $data = array(
            'uid' =>  $uid,
            'poster_uid' => $posterUid,
            'user_type' => $userType,
            'title' => $title,
            'content' => $content,
            'thumbnail' => $thumbnail,
            'images' => $feedAttachment,
            'local_images' => $localImages,
            'feed_type' => $feedType,
            'views' => '0',
            'feed_attachment' => $video,
            'status' => Utility::STATUS_ACTIVE
        );
        return self::create($data);
    }

    public  function newsToLiveFeed($newsUid,$posterUid,$userType,$liveFeedStatus)
    {

        $data = array(
            'uid' =>  $newsUid,
            'poster_uid' => $posterUid,
            'user_type' => $userType,
            'feed_type' => 'news',
            'views' => '0',
            'feed_attachment' => '',
            'status' => $liveFeedStatus
        );
        return self::create($data);
    }

    public  function updateLiveFeed($uid, $title,$content)
    {

        $data = array(
            'title' => $title,
            'content' => $content
        );
        return self::where('uid',$uid)
            ->update($data);
    }

    public  function updateLiveFeedStatus($uid, $liveFeedStatus)
    {

        $data = array(
            'status' => $liveFeedStatus,
        );
        return self::where('uid',$uid)
            ->update($data);
    }

    public function getNewsLiveFeed($uid){
        return self::where("uid" , $uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function updateImage( $uid,$column,$image)
    {

        $data = array(
            $column => $image
        );
        return self::where('uid',$uid)->where("uid" , $uid)
            ->update($data);
    }

    public  function updateLiveFeedImage( $uid,$images,$localImages)
    {

        $data = array(
            'images' => $images,
            'local_images' => $localImages

        );
        return self::where('uid',$uid)
            ->update($data);
    }


    public function deleteLiveFeed($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function updateLiveFeedViews($uid,$new_count){

        return Utility::updateViewsTable($this->table, $uid, $new_count);
    }

    public function getUniqueLiveFeed($uid){
        return self::where("uid" , $uid)->first();
    }

    public function hasPushedToCDN($uid,$column)
    {
        return Utility::updateAnyCDNStatus($this->table,$column, $uid, Utility::CDN_STATUS_YES);
    }

}

