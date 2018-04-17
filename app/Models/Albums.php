<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;


class Albums extends Model
{
    //
	protected $table = 'afrobt_albums';
    protected $guarded = [];


    public function getAlbums($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('album_name', 'asc')->get();
    }

    public function getRecentAlbums($status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getAlbum($slug){
        return self::where("slug" , $slug)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createAlbum( $name, $artistUid , $cover,$release_date){

        $slug = Utility::generateSlug($name, $this->table);

        $data = array(
            'album_name' => $name,
            'album_cover' => $cover,
            'artist_uid' => $artistUid,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug,
            'release_date' => $release_date
        );
        return self::create($data);
    }

    public function editAlbum($id,  $name, $artistUid,$release_date){
        $data = array(
            'album_name' => $name,
            'album_name' => $name,
            'artist_uid' => $artistUid,
            'release_date' => $release_date
        );
        return self::where("id" , $id)
            ->update($data);
    }

    public function editName($id,  $name){
        $data = array(
            'album_name' => $name
        );
        return self::where("id" , $id)
            ->update($data);
    }



    public function editCover($id,  $cover){
        $data = array(
            'album_cover' => $cover
        );
        return self::where("id" , $id)
            ->update($data);
    }


    public function editAlbumArtist($id,  $artistUid){
        $data = array(
            'artist_uid' => $artistUid
        );
        return self::where("id" , $id)
            ->update($data);
    }

    public function deleteAlbum($id){

        return self::where('id' , $id)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function artist(){
        return $this->belongsTo('App\User', 'artist_uid', 'uid');
    }

    public function user_album(){
        return $this->belongsTo('App\User', 'user_id', 'uid');
    }

    public function songs(){
        return $this->hasMany('App\Models\Music', 'album_id', 'id');
    }

    //USED IN LOADING MORE COMMENT
    public function user_artist(){
        return $this->belongsTo('App\User', 'user_id', 'uid');
    }

    //ALBUMS ADDED BY USER IN FRONTEND

    public function getUserAlbums($user_id,$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->where('artist_uid',$user_id)
            ->skip($start)->take($limit)
            ->orderBy('album_name', 'asc')->get();
    }

    public function getUserRecentAlbums($user_id,$status = "", $start = 0, $limit = 20){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)
            ->where('artist_uid',$user_id)
            ->skip($start)->take($limit)
            ->orderBy('created_at', 'desc')->get();
    }

    public function getUserAlbum($user_id,$slug){
        return self::where("slug" , $slug)
            ->where('artist_uid',$user_id)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public  function createUserAlbum( $name, $user_uid , $cover, $release_date,
                                      $cdn_status = Utility::CDN_STATUS_NO){

        $slug = Utility::generateSlug($name, $this->table);

        $data = array(
            'album_name' => $name,
            'album_cover' => $cover,
            'release_date' => $release_date,
            'artist_uid' => $user_uid,
            'status' => Utility::STATUS_ACTIVE,
            'slug' => $slug,
            'cdn_status' => $cdn_status
        );
        return self::create($data);
    }

    public function editUserAlbum($id,  $name, $release_date){
        $data = array(
            'release_date' => $release_date,
            'album_name' => $name
        );
        return self::where("id" , $id)
            ->update($data);
    }

    public function editUserName($id,  $name){
        $data = array(
            'album_name' => $name
        );
        return self::where("id" , $id)
            ->update($data);
    }



    public function editUserCover($id,  $cover){
        $data = array(
            'album_cover' => $cover
        );
        return self::where("id" , $id)
            ->update($data);
    }


    public function editUserAlbumArtist($id,  $artistUid){
        $data = array(
            'artist_uid' => $artistUid
        );
        return self::where("id" , $id)
            ->update($data);
    }

    public function deleteUserAlbum($id){

        return self::where('id' , $id)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function userArtist(){
        return $this->belongsTo('App\User', 'artist_uid', 'uid');
    }
    //END OF ALBUMS


    public function hasPushedToCDN($id)
    {
        return Utility::updateCDNStatusById($this->table, $id, Utility::CDN_STATUS_YES);
    }


}
