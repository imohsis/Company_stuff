<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;
use Auth;
class PlaylistFiles extends Model
{
    //
	protected $table = 'afrobt_playlist_files';

    protected $guarded = [];
    public function my_playlist(){
        return $this->belongsTo('App\Models\Playlist', 'playlist_uid', 'uid');
    }

    public function getSinglePlaylistFile($uid){
        return self::where("playlist_uid" , $uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->get();
    }

    public function getPlaylistFilesByPlaylist($uid){

        return self::where("playlist_uid" , $uid)
            ->where("status",Utility::STATUS_ACTIVE)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public  function createPlaylistFiles($playlist_uid, $file_uid){

        $data = array(
            'playlist_uid' => $playlist_uid,
            'file_uid' => $file_uid,
            'status' => Utility::STATUS_ACTIVE
        );
        return self::create($data);
    }

    public function deleteSongFile($id){

        return self::where('id' , $id)
            ->update(['status' => Utility::STATUS_DELETED] );
    }

}
