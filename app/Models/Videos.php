<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;

class Videos extends Model
{

	protected  $table = 'afrobt_videos';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getVideos($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getVideosByArtist($artistUid,$start = 0, $limit = 20){

        return self::where('status' , Utility::STATUS_ACTIVE)
            ->where('artist_uid',$artistUid)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getVideosByType($type, $start = 0, $limit = 20){

        return self::where('status' , Utility::STATUS_ACTIVE)
            ->where('type',$type)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getVideo($slug){
        return self::where("slug" , $slug)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createVideo( $type,$videoType, $fileName, $artistUid, $title,
        $videoCover, $link){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($title, $this->table);

            $data = array(
                'uid' => $uid,
                'admin_id' => Auth::user()->uid,
                'type' => $type,
                'video_type' => $videoType,
                'artist_uid' => $artistUid,
                'title' => $title,
                'file_name' => $fileName,
                'video_cover' => $videoCover,
                'link' => $link,
                'status' => Utility::STATUS_ACTIVE,
                'slug' => $slug,
                'acc_type' => Auth::user()->acc_type

            );

        return self::create($data);
    }

    public  function createUserVideo( $type,$videoType, $fileName, $title,
                                  $videoCover, $link){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($title, $this->table);

        $data = array(
            'uid' => $uid,
            'admin_id' => Auth::user()->uid,
            'type' => $type,
            'video_type' => $videoType,
            'artist_uid' => Auth::user()->uid,
            'title' => $title,
            'file_name' => $fileName,
            'video_cover' => $videoCover,
            'link' => $link,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug,
            'acc_type' => Auth::user()->acc_type

        );

        return self::create($data);
    }

    public function editVideo($uid,  $type, $artistUid, $title,$link){
        $data = array(
            'type' => $type,
            'artist_uid' => $artistUid,
            'title' => $title,
            'link' => $link,
            'status' => Utility::STATUS_ACTIVE

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function deleteVideo($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function editVideoCover($uid,  $videoCover){
        $data = array(
            'video_cover' => $videoCover

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function artist(){
        return $this->belongsTo('App\User', 'artist_uid', 'uid');
    }

    public function updateUserVideoViews($uid,$new_count){

        return Utility::updateViewsTable($this->table, $uid, $new_count);
    }

    public function hasPushedToCDN($uid)
    {
        return Utility::updateCDNStatus($this->table, $uid, Utility::CDN_STATUS_YES);
    }

}
