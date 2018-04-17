<?php

namespace App\models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Livetv extends Model
{
    //

    protected $table = 'afrobt_livetv';

    protected $guarded = [];

    public function poster_artist(){
        return $this->belongsTo('App\Models\Artist', 'poster_uid', 'uid');
    }

    public function livetv_news(){
        return $this->belongsTo('App\Models\News', 'uid', 'uid');
    }

    public function poster_user(){
        return $this->belongsTo('App\User', 'poster_uid', 'uid');
    }

    public function getLivetv($status = "", $start = 0, $limit = 30){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , Utility::STATUS_ACTIVE)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getActiveLiveVideo(){
        return self::where("video_status" , Utility::STATUS_ACTIVE)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getLivetvUpdates($lastID){

        return self::where('status' , Utility::STATUS_ACTIVE )
            ->where('id','>',$lastID)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getPosterLivetv($user_type,$start = 0, $limit = 20){

        return self::where("user_type" , $user_type)
            ->where("status",Utility::STATUS_ACTIVE )
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getUniquePosterLivetv($posterUid,$start = 0, $limit = 20){

        return self::where("poster_uid" , $posterUid)
            ->where("status",Utility::STATUS_ACTIVE )
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSingleLivetv($uid){
        return self::where("uid" , $uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createLivetv($posterUid,$userType,$title,$thumbnail,$livetvType,
                                    $livetvAttachment,$video_status)
    {

        $uid = Utility::generateUID($this->table);

        $data = array(
            'uid' =>  $uid,
            'poster_uid' => Auth::user()->uid,
            'user_type' => $userType,
            'title' => $title,
            'thumbnail' => $thumbnail,
            'video' => $livetvAttachment,
            'video_type' => $livetvType,
            'video_status' => $video_status,
            'views' => '0',
            'status' => Utility::STATUS_ACTIVE
        );
        return self::create($data);
    }


    public  function updateLivetv($uid, $title,$youtube_id)
    {

        $data = array(
            'title' => $title,
            'video' => $youtube_id
        );
        return self::where('uid',$uid)
            ->update($data);
    }

    public  function activateVideoToLive($uid,$status)
    {

        $data = array(
            'video_status' => $status
        );
        return self::where('uid',$uid)
            ->update($data);
    }

    public  function updateLivetvStatus($uid, $livetvStatus)
    {

        $data = array(
            'status' => $livetvStatus,
        );
        return self::where('uid',$uid)
            ->update($data);
    }

    public  function updateImage( $uid,$column,$image)
    {

        $data = array(
            $column => $image
        );
        return self::where('uid',$uid)->where("uid" , $uid)
            ->update($data);
    }

    public  function updateLivetvImage( $uid,$images,$localImages)
    {

        $data = array(
            'images' => $images,
            'local_images' => $localImages

        );
        return self::where('uid',$uid)
            ->update($data);
    }


    public function deleteLivetv($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function updateLivetvViews($uid,$new_count){

        return Utility::updateViewsTable($this->table, $uid, $new_count);
    }

    public function getUniqueLivetv($uid){
        return self::where("uid" , $uid)->first();
    }

    public function hasPushedToCDN($uid,$column)
    {
        return Utility::updateAnyCDNStatus($this->table,$column, $uid, Utility::CDN_STATUS_YES);
    }

}

