<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;

class Artists extends Model
{
    //
	protected $table = 'afrobt_artists';

    protected $guarded = [];


    public function getArtists($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('artist_name', 'asc')->get();
    }

    public function getRecentArtists($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getArtist($slug){
        return self::where("slug" , $slug)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createArtist( $artist_name, $avatar , $cdn_status = Utility::CDN_STATUS_NO){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($artist_name, $this->table);

        $data = array(
            'uid' => $uid,
            'artist_name' => $artist_name,
            'avatar' => $avatar,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug,
            'cdn_status' => $cdn_status
        );
        return self::create($data);
    }

    public function editArtist($uid,  $artist_name, $avatar){
        $data = array(
            'artist_name' => $artist_name,
            'avatar' => $avatar

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function editName($uid,  $artist_name){
        $data = array(
            'artist_name' => $artist_name

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function editAvatar($uid,  $avatar){
        $data = array(
            'avatar' => $avatar

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function deleteArtist($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function albums(){
        return $this->hasMany('App\Models\Albums', 'artist_uid', 'uid');
    }

    public function songs(){
        return $this->hasMany('App\Models\Music', 'artist_uid', 'uid');
    }

    public function videos(){
        return $this->hasMany('App\Models\Videos', 'artist_uid', 'uid');
    }


    public function hasPushedToCDN($uid)
    {
        return Utility::updateCDNStatus($this->table, $uid, Utility::CDN_STATUS_YES);
    }





}
