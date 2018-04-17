<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Utility;
use Auth;
class Playlist extends Model
{
    //
	protected $table = 'afrobt_playlist';
    protected $guarded = [];
    public $timestamps = false;

    public function createPlaylist($title,$type){
        $uid = Utility::generateUID($this->table);
        $slug = Utility::generateSlug($title, $this->table);
        $data = array(
            'uid' => $uid,
            'user_id' => Auth::user()->uid,
            'title' => $title,
            'type' => $type,
            'slug' => $slug,
            'status' => Utility::STATUS_ACTIVE
        );

        return self::create($data);

    }

    public function editPlaylist($uid, $title)
    {
        return self::where('uid' , $uid)
            ->update(['title' => $title] );
    }

    public function deletePlaylist($uid)
    {
        return self::where('uid' , $uid)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

    public function getAllPlaylist($status = "",$acc_type){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        if($acc_type == "") {
            return self::where('status', $status)
                ->orderBy('title', 'asc')->get();
        }else{
            return self::where('status', $status)->where('acc_type', $acc_type)
                ->orderBy('title', 'asc')->get();
        }
    }

    public function getAllUserPlaylist($status = ""){
        $status = ($status === "" ? Utility::STATUS_ACTIVE : $status);
        return self::where('status' , $status)->where('user_id' , Auth::user()->uid)
            ->orderBy('title', 'asc')->get();
    }

    public function getPlaylist($slug){
        return self::where('slug' , $slug)
            ->where("status", Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getUserPlaylist($slug){
        return self::where('slug' , $slug)->where('user_id' , Auth::user()->uid)
            ->where("status", Utility::STATUS_ACTIVE)
            ->get();
    }





}
