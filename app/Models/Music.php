<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;
use Auth;


class Music extends Model
{
    //
	protected $table = 'afrobt_music';
    protected $guarded = [];

    public function user(){
        return $this->belongsTo('App\User', 'user_id', 'uid');
    }

    public function getSongs($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('title', 'asc')->get();
    }

    public function getRecentSongs($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getSong($slug){
        return self::where("slug" , $slug)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getSongByUid($uid){
        return self::where("uid" , $uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->first();
    }

    public function getByGenre($genre,$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->where('genre_id' , $genre)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getByArtist($artistUid,$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->where('artist_uid' , $artistUid)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public  function createSong( $title, $genreId, $artistUid , $albumId,
                                  $filename, $duration, $cdn_status = Utility::CDN_STATUS_NO){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($title, $this->table);

        $data = array(
            'uid' => $uid,
            'title' => $title,
            'admin_uid' => Auth::user()->uid,
            'genre_id'=> $genreId,
            'album_id' => $albumId,
            'filename' => $filename,
            'artist_uid' => $artistUid,
            'duration' => $duration,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug,
            'cdn_status' => $cdn_status
        );
        return self::create($data);
    }

    public function editSong($uid,  $title, $artistUid, $genreId, $albumId){

        $data = array(
            'title' => $title,
            'artist_uid' => $artistUid,
            'genre_id' => $genreId,
            'album_id' => $albumId
        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function editSongFile($uid,  $filename, $duration){
        $data = array(
            'filename' => $filename,
            'duration' => $duration
        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function deleteSong($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function artist(){
        return $this->belongsTo('App\User', 'artist_uid', 'uid');
    }

    public function album(){
        return $this->belongsTo('App\Models\Albums', 'album_id', 'id');
    }

    public function admin(){
        return $this->belongsTo('App\User', 'admin_uid', 'uid');
    }

    //BEGIN OF USERS MUSIC METHODS

    public function getUserSongs($user_uid,$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->where('admin_uid',$user_uid)
            ->skip($start)->take($limit)
            ->orderBy('title', 'asc')->get();
    }

    public function getMassiveUserSongs($user_uid=[],$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->whereIn('admin_uid',$user_uid)
            ->skip($start)->take($limit)
            ->orderBy('title', 'asc')->get();
    }

    public function getUserRecentSongs($user_uid,$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->where('admin_uid',$user_uid)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getUserSong($user_uid,$uid){
        return self::where("uid" , $uid)
            ->where('admin_uid',$user_uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createUserSong( $title, $genreId, $albumId,
                                     $filename, $music_cover, $duration, $privacy, $cdn_status = Utility::CDN_STATUS_NO){

        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($title, $this->table);

        $data = array(
            'uid' => $uid,
            'title' => $title,
            'admin_uid' => Auth::user()->uid,
            'genre_id'=> $genreId,
            'album_id' => $albumId,
            'filename' => $filename,
            'music_cover' => $music_cover,
            'privacy' => $privacy,
            'artist_uid' => Auth::user()->uid,
            'duration' => $duration,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug,
            'cdn_status' => $cdn_status
        );
        return self::create($data);
    }

    public function editUserSong($uid,  $title, $genreId, $albumId,$privacy){

        $data = array(
            'title' => $title,
            'artist_uid' => Auth::user()->uid,
            'genre_id' => $genreId,
            'privacy' => $privacy,
            'album_id' => $albumId
        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function editUserSongFile($uid,  $filename, $duration){
        $data = array(
            'filename' => $filename,
            'duration' => $duration
        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function editMusicCover($uid,  $coverImg){
        $data = array(
            'avatar' => $coverImg

        );
        return self::where("uid" , $uid)
            ->update($data);
    }

    public function deleteUserSong($uid){

        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function updateUserSongViews($uid,$new_count){

        return Utility::updateViewsTable($this->table, $uid, $new_count);
    }

    //END OF USERS MUSIC METHODS

    public function hasPushedToCDN($uid)
    {
        return Utility::updateCDNStatus($this->table, $uid, Utility::CDN_STATUS_YES);
    }


}
